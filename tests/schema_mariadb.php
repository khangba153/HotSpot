<?php
declare(strict_types=1);

// A02 only: schema/constraints. Business routines and demo seed belong to A03.
// Every run creates a NEW database; no DROP/TRUNCATE/ALTER and no writes to db_hospot.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

$root = dirname(__DIR__);
$dbName = 'hotspot_test_a02_' . gmdate('Ymd_His') . '_' . bin2hex(random_bytes(4));
$results = [];
$pdo = null;
$draftBefore = null;
$draftAfter = null;
$version = '';

function check(string $name, bool $ok, mixed $detail = null): void
{
    global $results;
    $results[] = ['name' => $name, 'status' => $ok ? 'PASS' : 'FAIL', 'detail' => $detail];
    echo ($ok ? 'PASS ' : 'FAIL ') . $name . PHP_EOL;
}

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

function rejects(PDO $pdo, string $name, string $sql, array $values, int $expectedCode): void
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        check($name, false, 'Invalid data was accepted');
    } catch (PDOException $e) {
        $code = (int) ($e->errorInfo[1] ?? 0);
        check($name, $code === $expectedCode, ['expected_error' => $expectedCode, 'actual_error' => $code]);
    }
}

try {
    if (!extension_loaded('pdo_mysql')) throw new RuntimeException('pdo_mysql is required');
    $host = getenv('HOTSPOT_TEST_DB_HOST') ?: '127.0.0.1';
    if (!in_array($host, ['127.0.0.1', 'localhost'], true)) throw new RuntimeException('Tests require a local/CI server, never a shared remote host');
    $port = (int) (getenv('HOTSPOT_TEST_DB_PORT') ?: 3306);
    if ($port < 1 || $port > 65535) throw new RuntimeException('Invalid test port');
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4",
        getenv('HOTSPOT_TEST_DB_USER') ?: 'root', getenv('HOTSPOT_TEST_DB_PASSWORD') ?: '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
    $version = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
    check('Official MariaDB 10.4.32 engine', (bool) preg_match('/^10\.4\.32-MariaDB/', $version), $version);
    if (!preg_match('/^10\.4\.32-MariaDB/', $version)) throw new RuntimeException('Wrong engine/version; database was NOT created');
    $draftBefore = draftFingerprint($pdo);
    $guard = $pdo->prepare('SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME=?');
    $guard->execute([$dbName]);
    if ((int) $guard->fetchColumn()) throw new RuntimeException('Test database already exists; refusing to reuse it');
    if (!preg_match('/^hotspot_test_a02_[0-9]{8}_[0-9]{6}_[a-f0-9]{8}$/D', $dbName)) throw new RuntimeException('Unsafe test database name');
    $pdo->exec('CREATE DATABASE `' . $dbName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `' . $dbName . '`');
    $schema = file_get_contents($root . '/database/schema.sql');
    if ($schema === false) throw new RuntimeException('Missing schema.sql');
    $schema = preg_replace('/^\s*--[^\r\n]*/m', '', $schema);
    foreach (explode(';', (string) $schema) as $statement) {
        $statement = trim($statement);
        if ($statement === '') continue;
        if (!preg_match('/^(SET\s|CREATE TABLE\s)/i', $statement)) throw new RuntimeException('Unexpected statement in schema; only SET and CREATE TABLE allowed');
        $pdo->exec($statement);
    }
    check('Schema imported without removing constraints', true);
    check('CHECK enforcement stays enabled', (int) $pdo->query('SELECT @@check_constraint_checks')->fetchColumn() === 1);
    check('FK enforcement stays enabled', (int) $pdo->query('SELECT @@foreign_key_checks')->fetchColumn() === 1);
    check('Strict mode enabled for import/test connection', str_contains((string) $pdo->query('SELECT @@sql_mode')->fetchColumn(), 'STRICT_TRANS_TABLES'));
    check('Connection timezone UTC', $pdo->query('SELECT @@time_zone')->fetchColumn() === '+00:00');

    $tables = $pdo->query('SELECT TABLE_NAME,ENGINE,TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME')->fetchAll();
    $expected = ['categories','favorites','place_images','place_statuses','place_tags','places','provinces','review_statuses','reviews','roles','tags','users','wards'];
    $actual = array_column($tables, 'TABLE_NAME');
    sort($actual, SORT_STRING);
    sort($expected, SORT_STRING);
    check('Exactly the 13 approved tables', $actual === $expected);
    check('All tables use InnoDB/utf8mb4_unicode_ci', count(array_filter($tables, fn(array $t): bool => $t['ENGINE'] === 'InnoDB' && $t['TABLE_COLLATION'] === 'utf8mb4_unicode_ci')) === 13);
    $constraints = $pdo->query('SELECT TABLE_NAME,CONSTRAINT_NAME,CONSTRAINT_TYPE FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME,CONSTRAINT_NAME')->fetchAll();
    $counts = array_count_values(array_column($constraints, 'CONSTRAINT_TYPE'));
    check('13 PK / 15 FK / 7 UNIQUE / 15 CHECK retained', ($counts['PRIMARY KEY'] ?? 0) === 13 && ($counts['FOREIGN KEY'] ?? 0) === 15 && ($counts['UNIQUE'] ?? 0) === 7 && ($counts['CHECK'] ?? 0) === 15, $counts);
    check('No A03 routines introduced during A02', (int) $pdo->query("SELECT COUNT(*) FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA=DATABASE()")->fetchColumn() === 0);

    // Minimal fixtures are transactional test data, not the A03 demo/administrative seed.
    $pdo->beginTransaction();
    foreach ([
        "INSERT INTO roles VALUES('ADMIN','Admin fixture'),('MEMBER','Member fixture')",
        "INSERT INTO users(user_id,role_code,full_name,email,password_hash) VALUES(1,'ADMIN','Admin fixture','a@fixture.test','fixture_hash'),(2,'MEMBER','Member fixture','m@fixture.test','fixture_hash')",
        "INSERT INTO categories(category_id,name) VALUES(1,'Category fixture'),(2,'Old category fixture')",
        "INSERT INTO provinces(province_code,province_name) VALUES('01','Province A fixture'),('79','Province B fixture')",
        "INSERT INTO wards(ward_code,province_code,ward_name,ward_type) VALUES('00001','01','Ward A fixture','PHUONG'),('79001','79','Ward B fixture','XA')",
        "INSERT INTO place_statuses VALUES('PENDING','Pending',0),('APPROVED','Approved',1),('REJECTED','Rejected',0),('HIDDEN','Hidden',0)",
        "INSERT INTO review_statuses VALUES('VISIBLE','Visible',1),('HIDDEN','Hidden',0)",
        "INSERT INTO tags(tag_id,name,slug) VALUES(1,'Tag fixture','tag-fixture')",
        "INSERT INTO places(place_id,owner_id,category_id,province_code,title,description,address) VALUES(1,2,1,'01','Place fixture','Description fixture','Address fixture')",
        "INSERT INTO place_images(place_id,image_path,sort_order) VALUES(1,'fixture/image.jpg',1)",
        "INSERT INTO place_tags VALUES(1,1)",
        "INSERT INTO reviews(user_id,place_id,rating,content) VALUES(2,1,4,'Review fixture')",
        "INSERT INTO favorites(user_id,place_id) VALUES(2,1)",
    ] as $sql) $pdo->exec($sql);
    check('All 13 tables accept valid fixtures', true);
    $fkCases = [
        ['users role', "UPDATE users SET role_code='MISSING' WHERE user_id=2"],
        ['wards province', "UPDATE wards SET province_code='99' WHERE ward_code='00001'"],
        ['places owner', 'UPDATE places SET owner_id=999 WHERE place_id=1'],
        ['places category', 'UPDATE places SET category_id=999 WHERE place_id=1'],
        ['places province with NULL ward', "UPDATE places SET province_code='99' WHERE place_id=1"],
        ['places ward/province mismatch', "UPDATE places SET ward_code='79001' WHERE place_id=1"],
        ['places status', "UPDATE places SET status_code='MISSING' WHERE place_id=1"],
        ['images place', 'UPDATE place_images SET place_id=999'],
        ['place_tags place', 'UPDATE place_tags SET place_id=999'],
        ['place_tags tag', 'UPDATE place_tags SET tag_id=999'],
        ['reviews user', 'UPDATE reviews SET user_id=999'],
        ['reviews place', 'UPDATE reviews SET place_id=999'],
        ['reviews status', "UPDATE reviews SET status_code='MISSING'"],
        ['favorites user', 'UPDATE favorites SET user_id=999'],
        ['favorites place', 'UPDATE favorites SET place_id=999'],
    ];
    foreach ($fkCases as [$name, $sql]) rejects($pdo, 'FK rejects ' . $name, $sql, [], 1452);
    foreach ([
        ['users name', "UPDATE users SET full_name=' ' WHERE user_id=2"],
        ['users email', "UPDATE users SET email=' ' WHERE user_id=2"],
        ['categories name', "UPDATE categories SET name=' ' WHERE category_id=1"],
        ['ward type', "UPDATE wards SET ward_type='DISTRICT' WHERE ward_code='00001'"],
        ['places title', "UPDATE places SET title=' ' WHERE place_id=1"],
        ['places description', "UPDATE places SET description=' ' WHERE place_id=1"],
        ['places address', "UPDATE places SET address=' ' WHERE place_id=1"],
        ['coordinate pair', 'UPDATE places SET latitude=10,longitude=NULL WHERE place_id=1'],
        ['latitude range', 'UPDATE places SET latitude=91,longitude=106 WHERE place_id=1'],
        ['longitude range', 'UPDATE places SET latitude=10,longitude=181 WHERE place_id=1'],
        ['rejected reason', "UPDATE places SET status_code='REJECTED',rejection_reason=' ' WHERE place_id=1"],
        ['image order', 'UPDATE place_images SET sort_order=4'],
        ['image path', "UPDATE place_images SET image_path=' '"],
        ['review rating', 'UPDATE reviews SET rating=6'],
        ['review content', "UPDATE reviews SET content=' '"],
    ] as [$name, $sql]) rejects($pdo, 'CHECK rejects ' . $name, $sql, [], 4025);
    foreach ([
        ['name', 'UPDATE users SET full_name=? WHERE user_id=2'],
        ['review', 'UPDATE reviews SET content=?'],
        ['reason', "UPDATE places SET status_code='REJECTED',rejection_reason=? WHERE place_id=1"],
    ] as [$name, $sql]) rejects($pdo, 'CHECK rejects tab/newline-only ' . $name, $sql, ["\t\n"], 4025);
    foreach ([
        ['users email', "UPDATE users SET email='a@fixture.test' WHERE user_id=2"],
        ['categories name', "UPDATE categories SET name='Category fixture' WHERE category_id=2"],
        ['provinces name', "UPDATE provinces SET province_name='Province A fixture' WHERE province_code='79'"],
        ['ward province/code', "INSERT INTO wards VALUES('00001','01','Duplicate fixture','XA',1)"],
        ['image slot', "INSERT INTO place_images(place_id,image_path,sort_order) VALUES(1,'fixture/duplicate.jpg',1)"],
        ['tag slug', "INSERT INTO tags(name,slug) VALUES('Duplicate fixture','tag-fixture')"],
        ['review user/place', "INSERT INTO reviews(user_id,place_id,rating,content) VALUES(2,1,5,'Duplicate fixture')"],
        ['favorite PK', 'INSERT INTO favorites(user_id,place_id) VALUES(2,1)'],
        ['place_tags PK', 'INSERT INTO place_tags VALUES(1,1)'],
        ['roles PK', "INSERT INTO roles VALUES('MEMBER','Duplicate fixture')"],
    ] as [$name, $sql]) rejects($pdo, 'UNIQUE/PK rejects ' . $name, $sql, [], 1062);
    rejects($pdo, 'FK RESTRICT prevents deleting referenced category', 'DELETE FROM categories WHERE category_id=1', [], 1451);
    rejects($pdo, 'NOT NULL description enforced', 'UPDATE places SET description=NULL WHERE place_id=1', [], 1048);
    rejects($pdo, 'Strict mode rejects >120 character name', 'UPDATE users SET full_name=? WHERE user_id=2', [str_repeat('A', 121)], 1406);
    $pdo->exec("UPDATE places SET ward_code='00001',latitude=-90,longitude=180 WHERE place_id=1");
    check('Valid ward and boundary coordinate pair', true);
    $pdo->exec("UPDATE places SET ward_code=NULL,latitude=NULL,longitude=NULL WHERE place_id=1");
    check('Optional ward and paired NULL coordinates', true);
    $pdo->exec("UPDATE places SET status_code='REJECTED',rejection_reason='Reason fixture' WHERE place_id=1");
    check('REJECTED with meaningful reason remains accepted', true);
    $pdo->exec("INSERT INTO place_images(place_id,image_path,sort_order) VALUES(1,'fixture/2.jpg',2),(1,'fixture/3.jpg',3)");
    check('Three unique image slots accepted', (int) $pdo->query('SELECT COUNT(*) FROM place_images WHERE place_id=1')->fetchColumn() === 3);
    rejects($pdo, 'Fourth image rejected by CHECK', "INSERT INTO place_images(place_id,image_path,sort_order) VALUES(1,'fixture/4.jpg',4)", [], 4025);
    $pdo->rollBack();
    $remaining = 0;
    foreach ($expected as $table) $remaining += (int) $pdo->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
    check('All test fixture data rolled back', $remaining === 0, $remaining);
} catch (Throwable $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    // Error messages are intentionally withheld to avoid credentials/query values in artifacts.
    check('A02 execution completed', false, ['exception' => get_class($e), 'code' => (string) $e->getCode()]);
} finally {
    if ($pdo) {
        $draftAfter = draftFingerprint($pdo);
        check('Draft db_hospot structure/routines/counts unchanged', $draftBefore === $draftAfter,
            $draftBefore === null ? 'Draft absent on isolated CI server' : ['before' => $draftBefore, 'after' => $draftAfter]);
    }
    $failed = count(array_filter($results, fn(array $r): bool => $r['status'] === 'FAIL'));
    $report = ['task' => 'A02', 'engine' => $version, 'database' => $dbName,
        'total' => count($results), 'passed' => count($results) - $failed, 'failed' => $failed,
        'scope' => 'Schema/constraints only; A03 routines and demo seed NOT executed', 'checks' => $results];
    if (!is_dir(__DIR__ . '/results')) mkdir(__DIR__ . '/results', 0775, true);
    $path = __DIR__ . '/results/' . $dbName . '.json';
    file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . PHP_EOL);
    printf("A02: %d/%d passed, %d failed; test DB: %s\nReport: %s\n", $report['passed'], $report['total'], $failed, $dbName, $path);
    exit($failed === 0 ? 0 : 1);
}
