<?php
declare(strict_types=1);
function draftFingerprint(PDO $pdo): ?string
{
    $exists = $pdo->query("SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='db_hospot'")->fetchColumn();
    if (!(int) $exists) return null; // CI has no historical draft database.
    $snapshot = [];
    foreach ([
        "SELECT TABLE_NAME,ENGINE,TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_hospot' ORDER BY TABLE_NAME",
        "SELECT TABLE_NAME,COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,COLUMN_DEFAULT,EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='db_hospot' ORDER BY TABLE_NAME,ORDINAL_POSITION",
        "SELECT TABLE_NAME,CONSTRAINT_NAME,CONSTRAINT_TYPE FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA='db_hospot' ORDER BY TABLE_NAME,CONSTRAINT_NAME",
        "SELECT TABLE_NAME,INDEX_NAME,NON_UNIQUE,SEQ_IN_INDEX,COLUMN_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA='db_hospot' ORDER BY TABLE_NAME,INDEX_NAME,SEQ_IN_INDEX",
        "SELECT TABLE_NAME,CONSTRAINT_NAME,UPDATE_RULE,DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA='db_hospot' ORDER BY TABLE_NAME,CONSTRAINT_NAME",
        "SELECT ROUTINE_NAME,ROUTINE_DEFINITION,SQL_MODE,SECURITY_TYPE FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA='db_hospot' ORDER BY ROUTINE_NAME",
    ] as $query) $snapshot[] = $pdo->query($query)->fetchAll();
    $tables = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_hospot' AND TABLE_TYPE='BASE TABLE' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $quoted = str_replace('`', '``', (string) $table);
        $snapshot[] = [$table, (int) $pdo->query('SELECT COUNT(*) FROM `db_hospot`.`' . $quoted . '`')->fetchColumn()];
    }
    return hash('sha256', json_encode($snapshot, JSON_THROW_ON_ERROR));
}

function testConnection(): PDO
{
    $host=getenv('HOTSPOT_TEST_DB_HOST') ?: '127.0.0.1';
    if (!in_array($host,['127.0.0.1','localhost'],true)) throw new RuntimeException('Local/CI DB only');
    $port=(int)(getenv('HOTSPOT_TEST_DB_PORT') ?: 3306);
    if ($port<1 || $port>65535) throw new RuntimeException('Invalid port');
    $pdo=new PDO("mysql:host={$host};port={$port};charset=utf8mb4",getenv('HOTSPOT_TEST_DB_USER') ?: 'root',getenv('HOTSPOT_TEST_DB_PASSWORD') ?: '',
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    if (!preg_match('/^10\.4\.32-MariaDB/',(string)$pdo->query('SELECT VERSION()')->fetchColumn())) throw new RuntimeException('MariaDB 10.4.32 required');
    return $pdo;
}
function importSql(PDO $pdo,string $path): void
{
    $sql=file_get_contents($path);
    if ($sql===false) throw new RuntimeException('SQL file missing');
    $sql=preg_replace('/^\s*--[^\r\n]*/m','',$sql);
    $delimiter=';'; $buffer='';
    foreach (explode("\n",$sql) as $line) {
        if (preg_match('/^\s*DELIMITER\s+(\S+)\s*$/i',$line,$m)) {
            if (trim($buffer)!=='') throw new RuntimeException('Invalid delimiter position');
            $delimiter=$m[1];continue;
        }
        $buffer.=$line."\n";
        if (!str_ends_with(rtrim($buffer),$delimiter)) continue;
        $statement=trim(substr(rtrim($buffer),0,-strlen($delimiter)));$buffer='';
        if ($statement==='') continue;
        if (!preg_match('/^(SET\s|CREATE TABLE\s|CREATE PROCEDURE\s|INSERT INTO\s)/i',$statement)) throw new RuntimeException('Unsafe import statement');
        $pdo->exec($statement);
    }
    if (trim($buffer)!=='') throw new RuntimeException('Unterminated SQL');
}
function callProcedure(PDO $pdo,string $name,array $args=[]): array
{
    if (!preg_match('/^(KH|TV[234])_[A-Z_]+$/D',$name)) throw new RuntimeException('Unexpected routine name');
    $stmt=$pdo->prepare('CALL '.$name.'('.implode(',',array_fill(0,count($args),'?')).')');
    $sets=[];
    try {
        $stmt->execute($args);
        do { if ($stmt->columnCount()>0 && $stmt->getColumnMeta(0)!==false) $sets[]=$stmt->fetchAll(); } while ($stmt->nextRowset());
        return $sets;
    } finally { $stmt->closeCursor(); }
}
function seedDigest(PDO $pdo): string
{
    $data=[];
    $tables=$pdo->query('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $rows=$pdo->query('SELECT * FROM `'.str_replace('`','``',$table).'`')->fetchAll();
        $encoded=array_map(fn($row)=>json_encode($row,JSON_THROW_ON_ERROR),$rows);sort($encoded,SORT_STRING);
        $data[$table]=$encoded;
    }
    return hash('sha256',json_encode($data,JSON_THROW_ON_ERROR));
}
