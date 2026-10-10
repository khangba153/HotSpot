<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/support/mariadb.php';
$root=dirname(__DIR__); $pdo=null; $draftBefore=null; $results=[]; $seedCounts=[]; $seedBefore=null;
$dbName='hotspot_test_a03_'.gmdate('Ymd_His').'_'.bin2hex(random_bytes(4));
$names=['KH_CREATE_USER','KH_GET_USER_BY_EMAIL','KH_SET_FAVORITE','TV2_LIST_ACTIVE_CATEGORIES','TV2_SEARCH_APPROVED_PLACES','TV2_LIST_WARDS_BY_PROVINCE','TV3_SUBMIT_PLACE','TV3_ADD_PLACE_IMAGE','TV3_RESUBMIT_PLACE','TV4_SAVE_OR_UPDATE_REVIEW','TV4_SET_REVIEW_STATUS','TV4_SET_PLACE_STATUS'];
function record(string $routine,string $name,bool $ok,mixed $detail=null): void {
    global $results; $results[]=['routine'=>$routine,'name'=>$name,'status'=>$ok?'PASS':'FAIL','detail'=>$detail];
    echo ($ok?'PASS ':'FAIL ').$routine.' / '.$name.PHP_EOL;
}
function ensure(bool $ok,string $message='Unexpected result'): void { if (!$ok) throw new RuntimeException($message); }
function scalar(string $sql,array $args=[]): mixed { global $pdo; $s=$pdo->prepare($sql);$s->execute($args);return $s->fetchColumn(); }
function invoke(string $routine,array $args=[]): array { global $pdo;return callProcedure($pdo,$routine,$args); }
function expectError(string $routine,array $args,array $codes=[1644]): void {
    try { invoke($routine,$args); } catch (PDOException $e) { ensure(in_array((int)($e->errorInfo[1]??0),$codes,true),'Unexpected database error code '.(int)($e->errorInfo[1]??0));return; }
    throw new RuntimeException('Invalid input accepted');
}
function scenario(string $routine,string $name,callable $body,bool $transaction=true): void {
    global $pdo;
    try { if ($transaction) $pdo->beginTransaction();$body();record($routine,$name,true); }
    catch (Throwable $e) { record($routine,$name,false,['type'=>get_class($e),'code'=>(string)$e->getCode(),'assertion'=>$e instanceof PDOException?'SQL error (values withheld)':$e->getMessage()]); }
    finally { if ($pdo->inTransaction()) $pdo->rollBack(); }
}
function placeArgs(): array { return [2,1,'79',null,'Địa điểm kiểm thử','Mô tả hợp lệ','Địa chỉ minh họa',null,null]; }
function resubmitArgs(int $owner=2,int $place=19): array { return [$owner,$place,1,'79',null,'Tên sửa','Mô tả sửa','Địa chỉ sửa',null,null]; }
function search(array $change=[]): array { return invoke('TV2_SEARCH_APPROVED_PLACES',array_replace([null,null,null,null,null,1,12],$change)); }
try {
    $pdo=testConnection();$draftBefore=draftFingerprint($pdo);
    ensure((int)scalar('SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME=?',[$dbName])===0);
    $pdo->exec('CREATE DATABASE `'.$dbName.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `'.$dbName.'`');
    importSql($pdo,$root.'/database/schema.sql');
    $pdo->beginTransaction();
    importSql($pdo,$root.'/database/seed.sql');
    importSql($pdo,$root.'/database/seed_admin_full.sql');
    $pdo->commit();
    importSql($pdo,$root.'/database/routines.sql');
    $actual=$pdo->query('SELECT ROUTINE_NAME FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA=DATABASE() ORDER BY ROUTINE_NAME')->fetchAll(PDO::FETCH_COLUMN);
    $expected=$names;sort($expected);ensure($actual===$expected,'12 routine mapping mismatch');
    record('IMPORT','13 tables / 12 procedures / 3 per member',(int)scalar('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE()')===13);
    record('IMPORT','Strict mode captured in all 12 procedures',(int)scalar("SELECT COUNT(*) FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA=DATABASE() AND SQL_MODE LIKE '%STRICT_TRANS_TABLES%' AND SECURITY_TYPE='INVOKER'")===12);
    $memberText='';foreach (glob($root.'/database/routines_member*.sql') as $file) $memberText.=file_get_contents($file)."\n";
    record('IMPORT','Combined routine body equals four member files',str_ends_with(rtrim(str_replace("\r\n","\n",file_get_contents($root.'/database/routines.sql'))),rtrim(str_replace("\r\n","\n",$memberText))));
    foreach (['roles'=>2,'users'=>3,'categories'=>6,'provinces'=>34,'wards'=>3321,'place_statuses'=>4,'places'=>24,'place_images'=>24,'tags'=>8,'place_tags'=>24,'review_statuses'=>2,'reviews'=>18,'favorites'=>18] as $table=>$count) {
        $seedCounts[$table]=(int)scalar('SELECT COUNT(*) FROM `'.$table.'`');record('SEED',$table.' count '.$count,$seedCounts[$table]===$count);
    }
    foreach (['XA'=>2621,'PHUONG'=>687,'DAC_KHU'=>13] as $type=>$count) record('SEED','Official aggregate '.$type.' '.$count,(int)scalar('SELECT COUNT(*) FROM wards WHERE ward_type=?',[$type])===$count);
    foreach ([
        'Province/ward codes retain leading zero'=>"SELECT COUNT(*) FROM wards WHERE ward_code='00004' AND province_code='01'",
        'Hoang Sa Unicode normalization/type'=>"SELECT COUNT(*) FROM wards WHERE ward_code='20333' AND province_code='48' AND ward_type='DAC_KHU' AND ward_name='Đặc khu Hoàng Sa'",
    ] as $label=>$query) record('SEED',$label,(int)scalar($query)===1);
    foreach ([
        'No orphan province/ward'=>'SELECT COUNT(*) FROM wards w LEFT JOIN provinces p ON p.province_code=w.province_code WHERE p.province_code IS NULL',
        'Every demo place has 1-3 images'=>'SELECT COUNT(*) FROM places p WHERE (SELECT COUNT(*) FROM place_images i WHERE i.place_id=p.place_id) NOT BETWEEN 1 AND 3',
        'Every demo place has <=5 active seeded tags'=>'SELECT COUNT(*) FROM places p WHERE (SELECT COUNT(*) FROM place_tags pt WHERE pt.place_id=p.place_id)>5 OR EXISTS (SELECT 1 FROM place_tags pt JOIN tags t ON t.tag_id=pt.tag_id WHERE pt.place_id=p.place_id AND t.is_active<>1)',
        'Demo review and favorite refer to approved places'=>"SELECT (SELECT COUNT(*) FROM reviews r JOIN places p ON p.place_id=r.place_id WHERE p.status_code<>'APPROVED')+(SELECT COUNT(*) FROM favorites f JOIN places p ON p.place_id=f.place_id WHERE p.status_code<>'APPROVED')",
        'REJECTED demo has meaningful reason'=>"SELECT COUNT(*) FROM places WHERE status_code='REJECTED' AND (rejection_reason IS NULL OR rejection_reason NOT REGEXP '[^[:space:]]')",
        'No duplicate reviews/favorites'=>'SELECT (SELECT COUNT(*) FROM (SELECT user_id,place_id FROM reviews GROUP BY user_id,place_id HAVING COUNT(*)>1) r)+(SELECT COUNT(*) FROM (SELECT user_id,place_id FROM favorites GROUP BY user_id,place_id HAVING COUNT(*)>1) f)',
    ] as $label=>$query) record('SEED',$label,(int)scalar($query)===0);
    record('SEED','All demo password values are hashes',(int)scalar("SELECT COUNT(*) FROM users WHERE password_hash LIKE '$2y$%' AND LENGTH(password_hash)=60")===3);
    $seedBefore=seedDigest($pdo);

    $r='KH_CREATE_USER';
    scenario($r,'Valid registration / MEMBER / normalized email',function()use($r){$s=invoke($r,[' Tên Việt ',' NEW@fixture.test ',password_hash('local-test-only',PASSWORD_BCRYPT)]);$id=$s[0][0]['user_id'];ensure(scalar('SELECT role_code FROM users WHERE user_id=?',[$id])==='MEMBER');ensure(scalar('SELECT email FROM users WHERE user_id=?',[$id])==='new@fixture.test');});
    foreach ([['', 'new@fixture.test','hash'],["\t\n",'new@fixture.test','hash'],['Name',null,'hash'],['Name',"\t\n",'hash'],['Name','new@fixture.test',null],['Name','new@fixture.test',"\t\n"]] as $i=>$args) scenario($r,'Missing/whitespace input '.$i,fn()=>expectError($r,$args));
    scenario($r,'Case-insensitive duplicate email',fn()=>expectError($r,['Name','MEMBER1@HOTSPOT.TEST','hash'],[1062]));
    scenario($r,'120 Unicode character boundary',function()use($r){invoke($r,[str_repeat('Đ',120),'long@fixture.test','hash']);ensure((int)scalar("SELECT CHAR_LENGTH(full_name) FROM users WHERE email='long@fixture.test'")===120);});
    scenario($r,'121 characters rejected, no truncation',fn()=>expectError($r,[str_repeat('Đ',121),'long@fixture.test','hash'],[1406]));

    $r='KH_GET_USER_BY_EMAIL';
    scenario($r,'Case/space normalized match',function()use($r){$s=invoke($r,[' MEMBER1@HOTSPOT.TEST ']);ensure(count($s[0])===1 && (int)$s[0][0]['user_id']===2 && isset($s[0][0]['password_hash']));});
    scenario($r,'Unknown email returns empty result',fn()=>ensure(invoke($r,['none@fixture.test'])[0]===[]));
    scenario($r,'NULL input rejected',fn()=>expectError($r,[null]));
    scenario($r,'Whitespace input rejected',fn()=>expectError($r,["\t\n"]));
    scenario($r,'254 character search boundary',fn()=>ensure(invoke($r,[str_repeat('a',254)])[0]===[]));
    scenario($r,'255 character input rejected',fn()=>expectError($r,[str_repeat('a',255)],[1406]));

    $r='KH_SET_FAVORITE';
    scenario($r,'Desired 1 idempotent, desired 0 idempotent',function()use($r){invoke($r,[2,2,1]);invoke($r,[2,2,1]);ensure((int)scalar('SELECT COUNT(*) FROM favorites WHERE user_id=2 AND place_id=2')===1);invoke($r,[2,2,0]);invoke($r,[2,2,0]);ensure((int)scalar('SELECT COUNT(*) FROM favorites WHERE user_id=2 AND place_id=2')===0);});
    foreach ([null,2,-1] as $flag) scenario($r,'Invalid desired flag '.var_export($flag,true),fn()=>expectError($r,[2,1,$flag]));
    scenario($r,'Unknown user rejected',fn()=>expectError($r,[999,1,1]));
    foreach ([19,22,24,999] as $id) scenario($r,'Cannot add non-public/missing place '.$id,fn()=>expectError($r,[2,$id,1]));
    scenario($r,'Removal permitted after hiding / absent item',function()use($r){invoke('TV4_SET_PLACE_STATUS',[1,1,'HIDDEN',null]);invoke($r,[2,1,0]);invoke($r,[2,999,0]);ensure((int)scalar('SELECT COUNT(*) FROM favorites WHERE user_id=2 AND place_id=1')===0);});

    $r='TV2_LIST_ACTIVE_CATEGORIES';
    scenario($r,'Six active categories',fn()=>ensure(count(invoke($r)[0])===6));
    scenario($r,'Inactive categories excluded / no active boundary',function()use($r){global $pdo;$pdo->exec('UPDATE categories SET is_active=0');ensure(invoke($r)[0]===[]);});
    scenario($r,'Wrong argument count rejected',fn()=>expectError($r,[1],[1318]));

    $r='TV2_SEARCH_APPROVED_PLACES';
    scenario($r,'Two result sets / total count / no hidden review aggregate',function(){ $s=search();ensure(count($s)===2 && count($s[0])===12 && (int)$s[1][0]['total_count']===18);$s=search([0=>'demo 03']);ensure(count($s[0])===1 && (int)$s[0][0]['review_count']===0 && $s[0][0]['average_rating']===null);});
    scenario($r,'150-character keyword boundary / 151 rejected',function()use($r){ensure(search([0=>str_repeat('a',150)])[0]===[]);expectError($r,[str_repeat('a',151),null,null,null,null,1,12],[1406]);});
    scenario($r,'Pagination page 2, stable total',function(){ $s=search([5=>2]);ensure(count($s[0])===6 && (int)$s[1][0]['total_count']===18);});
    scenario($r,'Out-of-range page still returns count',function(){ $s=search([5=>2147483647]);ensure($s[0]===[] && (int)$s[1][0]['total_count']===18);});
    scenario($r,'Clamp page <=0 and size 0/31; NULL defaults',function(){ensure(count(search([5=>-1,6=>0])[0])===1);ensure(count(search([6=>31])[0])===18);ensure(count(search([5=>null,6=>null])[0])===12);});
    scenario($r,'Invalid/unmatched filters return zero',function(){foreach([[1=>999],[2=>'99'],[3=>'99999'],[4=>'not-a-tag'],[0=>'unmatched-text']] as $f){$s=search($f);ensure($s[0]===[] && (int)$s[1][0]['total_count']===0);}});
    scenario($r,'Category/province/tag filters consistent with count',function(){ $s=search([1=>1,2=>'79',4=>'check-in']);ensure(count($s[0])===1 && (int)$s[1][0]['total_count']===1);});
    scenario($r,'Ward filter and mismatch',function(){global $pdo;$ward=scalar("SELECT ward_code FROM wards WHERE province_code='79' LIMIT 1");$pdo->prepare('UPDATE places SET ward_code=? WHERE place_id=1')->execute([$ward]);ensure((int)search([2=>'79',3=>$ward])[1][0]['total_count']===1);ensure((int)search([2=>'01',3=>$ward])[1][0]['total_count']===0);});
    scenario($r,'Inactive category keeps old public posts',function(){global $pdo;$pdo->exec('UPDATE categories SET is_active=0 WHERE category_id=1');ensure((int)search([1=>1])[1][0]['total_count']===3);});
    scenario($r,'Native bound SQL injection string is data',fn()=>ensure((int)search([0=>"' OR 1=1 --"])[1][0]['total_count']===0));
    scenario($r,'Multiple consecutive CALL and query / cursor closed',function(){for($i=0;$i<4;$i++){ensure(count(search())===2);invoke('TV2_LIST_ACTIVE_CATEGORIES');ensure((int)scalar('SELECT 1')===1);}});

    $r='TV2_LIST_WARDS_BY_PROVINCE';
    scenario($r,'Valid province only / sorted list',function()use($r){$s=invoke($r,['01'])[0];ensure(count($s)>0);foreach($s as $row)ensure($row['province_code']==='01');ensure(count($s)===(int)scalar("SELECT COUNT(*) FROM wards WHERE province_code='01' AND is_active=1"));});
    scenario($r,'Unknown province gives empty list',fn()=>ensure(invoke($r,['99'])[0]===[]));
    scenario($r,'NULL/whitespace province rejected',function()use($r){expectError($r,[null]);expectError($r,["\t\n"]);});
    scenario($r,'Inactive ward omitted',function()use($r){global $pdo;$pdo->exec("UPDATE wards SET is_active=0 WHERE province_code='01'");ensure(invoke($r,['01'])[0]===[]);});
    scenario($r,'10-character code boundary',fn()=>ensure(invoke($r,['1234567890'])[0]===[]));

    $r='TV3_SUBMIT_PLACE';
    scenario($r,'Valid owner / PENDING / optional ward and coordinates',function()use($r){$id=invoke($r,placeArgs())[0][0]['place_id'];ensure(scalar('SELECT status_code FROM places WHERE place_id=?',[$id])==='PENDING');ensure(scalar('SELECT ward_code FROM places WHERE place_id=?',[$id])===null);});
    scenario($r,'Admin can submit own content',function()use($r){$a=placeArgs();$a[0]=1;$id=invoke($r,$a)[0][0]['place_id'];ensure((int)scalar('SELECT owner_id FROM places WHERE place_id=?',[$id])===1);});
    foreach ([4,5,6] as $field) scenario($r,'Whitespace text field '.$field,function()use($r,$field){$a=placeArgs();$a[$field]="\t\n";expectError($r,$a);});
    scenario($r,'Unknown owner FK enforced',function()use($r){$a=placeArgs();$a[0]=999;expectError($r,$a,[1452]);});
    scenario($r,'Inactive or missing category rejected',function()use($r){global $pdo;$pdo->exec('UPDATE categories SET is_active=0 WHERE category_id=1');expectError($r,placeArgs());$a=placeArgs();$a[1]=999;expectError($r,$a);});
    scenario($r,'Inactive province/ward and mismatch rejected',function()use($r){global $pdo;$a=placeArgs();$a[3]=scalar("SELECT ward_code FROM wards WHERE province_code='01' LIMIT 1");expectError($r,$a);$a[3]=scalar("SELECT ward_code FROM wards WHERE province_code='79' LIMIT 1");$pdo->prepare('UPDATE wards SET is_active=0 WHERE ward_code=?')->execute([$a[3]]);expectError($r,$a);$pdo->exec("UPDATE provinces SET is_active=0 WHERE province_code='79'");expectError($r,placeArgs());});
    scenario($r,'Valid ward / coordinate limits',function()use($r){$a=placeArgs();$a[3]=scalar("SELECT ward_code FROM wards WHERE province_code='79' LIMIT 1");$a[7]=-90;$a[8]=180;invoke($r,$a);$a[7]=90;$a[8]=-180;invoke($r,$a);});
    foreach ([[10,null],[91,106],[10,181]] as $i=>$coords) scenario($r,'Invalid coordinate pair/range '.$i,function()use($r,$coords){$a=placeArgs();$a[7]=$coords[0];$a[8]=$coords[1];expectError($r,$a,[1644,4025]);});
    scenario($r,'180-character title valid; 181 rejected',function()use($r){$a=placeArgs();$a[4]=str_repeat('Đ',180);invoke($r,$a);$a[4].='Đ';expectError($r,$a,[1406]);});

    $r='TV3_ADD_PLACE_IMAGE';
    scenario($r,'Valid slot 2/3; fourth slot and duplicate rejected',function()use($r){invoke($r,[2,19,'fixture/two.jpg',2]);invoke($r,[2,19,'fixture/three.jpg',3]);ensure((int)scalar('SELECT COUNT(*) FROM place_images WHERE place_id=19')===3);expectError($r,[2,19,'fixture/four.jpg',4]);expectError($r,[2,19,'fixture/dup.jpg',1],[1062]);});
    foreach ([[3,19,'x',2],[1,19,'x',2],[2,1,'x',2],[3,24,'x',2],[2,999,'x',2],[2,19,"\t\n",2],[2,19,'x',0],[2,19,'x',null]] as $i=>$a) scenario($r,'Invalid permission/status/input '.$i,fn()=>expectError($r,$a));
    scenario($r,'Rejected owner can edit images',fn()=>invoke($r,[3,22,'fixture/new.jpg',2]));
    scenario($r,'500-character path boundary and 501 rejected',function()use($r){invoke($r,[2,19,str_repeat('a',500),2]);expectError($r,[2,19,str_repeat('a',501),3],[1406]);});

    $r='TV3_RESUBMIT_PLACE';
    scenario($r,'Rejected owner -> PENDING and reason cleared',function()use($r){invoke($r,resubmitArgs(3,22));ensure(scalar('SELECT status_code FROM places WHERE place_id=22')==='PENDING' && scalar('SELECT rejection_reason FROM places WHERE place_id=22')===null);});
    scenario($r,'Unchanged PENDING resubmit is valid',function()use($r){invoke($r,resubmitArgs());invoke($r,resubmitArgs());});
    scenario($r,'Keep old inactive category; cannot select different inactive category',function()use($r){global $pdo;$pdo->exec('UPDATE categories SET is_active=0 WHERE category_id IN(1,2)');invoke($r,resubmitArgs());$a=resubmitArgs();$a[2]=2;expectError($r,$a);});
    foreach ([[3,19],[1,19],[2,1],[3,24],[2,999]] as [$owner,$id]) scenario($r,'Cannot edit other/public/hidden/missing '.$owner.'/'.$id,fn()=>expectError($r,resubmitArgs($owner,$id)));
    scenario($r,'Whitespace title and mismatched ward rejected',function()use($r){$a=resubmitArgs();$a[5]="\t\n";expectError($r,$a);$a=resubmitArgs();$a[4]=scalar("SELECT ward_code FROM wards WHERE province_code='01' LIMIT 1");expectError($r,$a);});
    scenario($r,'Valid coordinate boundary; invalid pair rejected',function()use($r){$a=resubmitArgs();$a[8]=90;$a[9]=-180;invoke($r,$a);$a[9]=null;expectError($r,$a);});

    $r='TV4_SAVE_OR_UPDATE_REVIEW';
    scenario($r,'Insert once / update same review / rating 1 and 5',function()use($r){$s=invoke($r,[2,1,1,'Review one']);$id=$s[0][0]['review_id'];$s=invoke($r,[2,1,5,'Review five']);ensure($s[0][0]['review_id']===$id && (int)$s[0][0]['rating']===5);ensure((int)scalar('SELECT COUNT(*) FROM reviews WHERE user_id=2 AND place_id=1')===1);});
    scenario($r,'Editing HIDDEN keeps HIDDEN',function()use($r){$s=invoke($r,[3,3,5,'Updated hidden']);ensure($s[0][0]['status_code']==='HIDDEN');});
    foreach ([[2,1,0,'x'],[2,1,6,'x'],[2,1,null,'x'],[2,1,3,"\t\n"],[2,1,3,null],[2,19,3,'x'],[2,22,3,'x'],[2,24,3,'x'],[2,999,3,'x']] as $i=>$a) scenario($r,'Invalid rating/content/non-public '.$i,fn()=>expectError($r,$a));
    scenario($r,'Unknown author FK enforced',fn()=>expectError($r,[999,1,3,'Review'],[1452]));
    scenario($r,'65535-byte content boundary',fn()=>invoke($r,[2,1,5,str_repeat('A',65535)]));
    scenario($r,'65536-byte content rejected without truncation',fn()=>expectError($r,[2,1,5,str_repeat('A',65536)],[1406]));
    scenario($r,'Admin reviews as member',fn()=>invoke($r,[1,1,5,'Admin own review']));

    $r='TV4_SET_REVIEW_STATUS';
    scenario($r,'Admin hide/restore / repeated desired status',function()use($r){$id=scalar('SELECT review_id FROM reviews WHERE user_id=3 AND place_id=1');invoke($r,[1,$id,'HIDDEN']);invoke($r,[1,$id,'HIDDEN']);ensure(scalar('SELECT status_code FROM reviews WHERE review_id=?',[$id])==='HIDDEN');invoke($r,[1,$id,'VISIBLE']);ensure(scalar('SELECT status_code FROM reviews WHERE review_id=?',[$id])==='VISIBLE');});
    foreach ([[2,1,'HIDDEN'],[999,1,'HIDDEN'],[1,999,'HIDDEN'],[1,1,'APPROVED'],[1,1,null]] as $i=>$a) scenario($r,'Invalid role/id/status '.$i,fn()=>expectError($r,$a));
    scenario($r,'Canonical status despite case/outer spaces',function()use($r){ensure(invoke($r,[1,1,' hidden '])[0][0]['status_code']==='HIDDEN');});
    scenario($r,'Hidden place review can be moderated but not searched',function()use($r){invoke('TV4_SET_PLACE_STATUS',[1,1,'HIDDEN',null]);invoke($r,[1,1,'HIDDEN']);ensure((int)search([0=>'demo 01'])[1][0]['total_count']===0);});

    $r='TV4_SET_PLACE_STATUS';
    scenario($r,'PENDING approve -> hide -> restore',function()use($r){foreach(['APPROVED','HIDDEN','APPROVED'] as $status){invoke($r,[1,19,$status,null]);ensure(scalar('SELECT status_code FROM places WHERE place_id=19')===$status);}});
    scenario($r,'Canonical status despite case/outer spaces',function()use($r){ensure(invoke($r,[1,19,' approved ',null])[0][0]['status_code']==='APPROVED');});
    scenario($r,'PENDING reject with reason, resubmit clears, approve clears',function()use($r){invoke($r,[1,19,'REJECTED',' Reason ']);ensure(scalar('SELECT rejection_reason FROM places WHERE place_id=19')==='Reason');invoke('TV3_RESUBMIT_PLACE',resubmitArgs());invoke($r,[1,19,'APPROVED',null]);ensure(scalar('SELECT rejection_reason FROM places WHERE place_id=19')===null);});
    foreach ([[2,19,'APPROVED',null],[999,19,'APPROVED',null],[1,999,'APPROVED',null],[1,19,null,null],[1,19,'MISSING',null],[1,19,'REJECTED',null],[1,19,'REJECTED',"\t\n"],[1,22,'APPROVED',null],[1,1,'APPROVED',null],[1,19,'HIDDEN',null],[1,24,'REJECTED','x']] as $i=>$a) scenario($r,'Invalid permission/transition/reason '.$i,fn()=>expectError($r,$a));
    scenario($r,'Cannot approve with zero images; three images accepted',function()use($r){global $pdo;$pdo->exec('DELETE FROM place_images WHERE place_id=19');expectError($r,[1,19,'APPROVED',null]);foreach([1,2,3] as $slot)invoke('TV3_ADD_PLACE_IMAGE',[2,19,'fixture/'.$slot.'.jpg',$slot]);invoke($r,[1,19,'APPROVED',null]);});

    foreach (['KH_CREATE_USER'=>['Name','outside@fixture.test','hash'],'KH_SET_FAVORITE'=>[2,1,1],'TV3_SUBMIT_PLACE'=>placeArgs(),'TV3_ADD_PLACE_IMAGE'=>[2,19,'fixture.jpg',2],'TV3_RESUBMIT_PLACE'=>resubmitArgs(),'TV4_SAVE_OR_UPDATE_REVIEW'=>[2,1,3,'Review'],'TV4_SET_REVIEW_STATUS'=>[1,1,'HIDDEN'],'TV4_SET_PLACE_STATUS'=>[1,19,'APPROVED',null]] as $r=>$args) scenario($r,'Reject write without caller transaction',fn()=>expectError($r,$args),false);
    scenario('TRANSACTION','Atomic submit/images/tags rollback after duplicate image failure',function(){global $pdo;$id=invoke('TV3_SUBMIT_PLACE',placeArgs())[0][0]['place_id'];invoke('TV3_ADD_PLACE_IMAGE',[2,$id,'fixture.jpg',1]);$pdo->prepare('INSERT INTO place_tags VALUES(?,1)')->execute([$id]);expectError('TV3_ADD_PLACE_IMAGE',[2,$id,'duplicate.jpg',1],[1062]);ensure($pdo->inTransaction(),'Routine committed caller transaction');});
    record('TRANSACTION','All scenarios restore exact seed rows',seedDigest($pdo)===$seedBefore);
    scenario('CONCURRENCY','Parent lock blocks concurrent moderation until caller rollback',function()use($dbName){global $pdo;$other=testConnection();$other->exec('USE `'.$dbName.'`');$other->exec('SET SESSION innodb_lock_wait_timeout=1');$other->beginTransaction();try{invoke('TV3_RESUBMIT_PLACE',resubmitArgs());try{callProcedure($other,'TV4_SET_PLACE_STATUS',[1,19,'APPROVED',null]);throw new RuntimeException('Concurrent moderation bypassed parent lock');}catch(PDOException $e){ensure((int)$e->errorInfo[1]===1205);}finally{if($other->inTransaction())$other->rollBack();}}finally{if($other->inTransaction())$other->rollBack();}});
    record('TRANSACTION','Seed still unchanged after lock test',seedDigest($pdo)===$seedBefore);
} catch (Throwable $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    record('RUNNER','Import/execution',false,['type'=>get_class($e),'code'=>(string)$e->getCode(),'message'=>$e instanceof PDOException?'SQL error (values withheld)':$e->getMessage()]);
} finally {
    if ($pdo) {try{record('SAFETY','db_hospot metadata/routines/counts unchanged',draftFingerprint($pdo)===$draftBefore);}catch(Throwable $e){record('SAFETY','Draft verification failed',false);}}
    $fail=count(array_filter($results,fn($r)=>$r['status']==='FAIL'));
    $summary=[];foreach($results as $r){$summary[$r['routine']][$r['status']]=($summary[$r['routine']][$r['status']]??0)+1;}
    $report=['task'=>'A03','database'=>$dbName,'engine'=>$pdo?(string)$pdo->query('SELECT VERSION()')->fetchColumn():null,'total'=>count($results),'passed'=>count($results)-$fail,'failed'=>$fail,'seed_counts'=>$seedCounts,'per_routine'=>$summary,'checks'=>$results];
    if(!is_dir(__DIR__.'/results'))mkdir(__DIR__.'/results',0775,true);
    $file=__DIR__.'/results/'.$dbName.'.json';file_put_contents($file,json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR).PHP_EOL);
    echo 'A03: '.$report['passed'].'/'.$report['total'].' PASS; '.$fail.' FAIL; DB '.$dbName.PHP_EOL.'Report: '.$file.PHP_EOL;
    exit($fail?1:0);
}
