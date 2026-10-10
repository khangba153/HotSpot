<?php
/**
 * Build SQL to import all 34 provinces and 3321 wards from a verified public JSON snapshot.
 * Usage:
 *   php tools/generate_admin_seed.php
 *   php tools/generate_admin_seed.php /path/to/Vietnamese-Administrative-Units-Dataset.json
 * Generated file: database/seed_admin_full.sql. Import ONLY into a new isolated test DB after seed.sql.
 * Reference: https://github.com/mantisvn/Vietnamese-Administrative-Units-Dataset
 * Official comparator: Decision 19/2025/QD-TTg.
 */
declare(strict_types=1);

if (!class_exists('Normalizer') || !function_exists('mb_strtolower')) { fwrite(STDERR, "PHP intl is required for NFC administrative names.\n"); exit(1); }
$source = 'https://raw.githubusercontent.com/mantisvn/Vietnamese-Administrative-Units-Dataset/main/Vietnamese-Administrative-Units-Dataset.json';
$input = $argv[1] ?? dirname(__DIR__).'/database/sources/administrative-units-2025.json';
if (filter_var($input, FILTER_VALIDATE_URL)) {
    if ($input !== $source) {
        fwrite(STDERR, "Only the known dataset URL is permitted.\n");
        exit(1);
    }
    $options = ['http' => ['timeout' => 25, 'user_agent' => 'HotSpotAcademicProject/1.0']];
    $json = @file_get_contents($input, false, stream_context_create($options));
    if ($json === false) {
        fwrite(STDERR, "Could not fetch dataset. Download JSON from GitHub, then rerun with local JSON filename.\n");
        exit(1);
    }
} else {
    $json = @file_get_contents($input);
    if ($json === false) { fwrite(STDERR, "Could not read: {$input}\n"); exit(1); }
}
try {
    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
} catch (Throwable $e) { fwrite(STDERR, "Invalid JSON\n"); exit(1); }
$provinces = $data['provinces'] ?? [];
$wards = $data['wards'] ?? [];
if (count($provinces) !== 34 || count($wards) !== 3321) {
    fwrite(STDERR, 'Unexpected row counts: ' . count($provinces) . ' provinces / ' . count($wards) . " wards; stopped.\n");
    exit(1);
}
function esc(string $s): string {
    return "'" . str_replace(["\\", "'"], ["\\\\", "''"], $s) . "'";
}
$officialCodes = ['01','04','08','11','12','14','15','19','20','22','24','25','31','33','37','38','40','42','44','46','48','51','52','56','66','68','75','79','80','82','86','91','92','96'];
$provinceById = []; $codes = []; $rows = [];
foreach ($provinces as $p) {
    if (!isset($p['id'],$p['code'],$p['name'])) { throw new RuntimeException('Bad province row'); }
    $id=(int)$p['id']; $code=(string)$p['code'];
    if (!preg_match('/^.{1,120}$/us', (string)$p['name'])) throw new RuntimeException('Invalid province name length');
    if (!in_array($code, $officialCodes, true) || isset($codes[$code]) || isset($provinceById[$id])) {
        throw new RuntimeException('Invalid or duplicate province code: '.$code);
    }
    $codes[$code]=true; $provinceById[$id]=$code;
    $rows[]='('.esc($code).','.esc(Normalizer::normalize((string)$p['name'], Normalizer::FORM_C)).',1)';
}
if (count($codes)!==34) { throw new RuntimeException('Province set mismatch'); }
$output = "-- Generated Vietnamese administrative units. Source: {$source}\n";
$output .= "-- Check official Decision 19/2025/QD-TTg before refreshing for future changes.\n";
$output .= "SET NAMES utf8mb4;\n";
$output .= "INSERT INTO provinces(province_code,province_name,is_active) VALUES\n" . implode(",\n",$rows) . "\nON DUPLICATE KEY UPDATE province_name=VALUES(province_name);\n\n";
$rows = []; $seenWards=[];
foreach ($wards as $w) {
    if (!isset($w['code'],$w['name'],$w['province_id'])) { throw new RuntimeException('Bad ward row'); }
    $code=(string)$w['code'];
    $provinceCode=$provinceById[(int)$w['province_id']] ?? null;
    if ($provinceCode === null || !preg_match('/^[0-9]{5}$/D',$code) || isset($seenWards[$code])) {
        throw new RuntimeException('Bad ward/province reference or repeated ward: '.$code);
    }
    $seenWards[$code]=true;
    $name=Normalizer::normalize((string)$w['name'], Normalizer::FORM_C);
    if (!preg_match('/^(Phường|Xã|Đặc khu)\s/iu', $name, $kind)) throw new RuntimeException('Unknown ward type');
    if (!preg_match('/^.{1,150}$/us', $name)) throw new RuntimeException('Invalid ward name length');
    $prefix = mb_strtolower($kind[1],'UTF-8');
    $type = ['phường'=>'PHUONG','xã'=>'XA','đặc khu'=>'DAC_KHU'][$prefix];
    $name = ['PHUONG'=>'Phường','XA'=>'Xã','DAC_KHU'=>'Đặc khu'][$type] . substr($name,strlen($kind[1]));
    $rows[]='('.esc($code).','.esc($provinceCode).','.esc($name).','.esc($type).',1)';
}
foreach (array_chunk($rows,300) as $chunk) {
    $output .= "INSERT INTO wards(ward_code,province_code,ward_name,ward_type,is_active) VALUES\n";
    $output .= implode(",\n",$chunk) . "\nON DUPLICATE KEY UPDATE province_code=VALUES(province_code), ward_name=VALUES(ward_name), ward_type=VALUES(ward_type);\n\n";
}
$output .= "-- Verification queries: SELECT COUNT(*) FROM provinces; SELECT COUNT(*) FROM wards;\n";
$destination=dirname(__DIR__).'/database/seed_admin_full.sql';
if (file_put_contents($destination,$output) === false) { throw new RuntimeException('Failed to write output SQL'); }
echo "Generated {$destination} (34 provinces, 3321 wards)\n";
