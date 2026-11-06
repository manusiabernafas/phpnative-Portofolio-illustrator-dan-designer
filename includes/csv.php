<?php
/** Sel CSV aman: nilai yang diawali = + - @ (atau tab/CR) diberi tanda petik agar tidak dijalankan sebagai rumus di Excel. */
function sel_csv($v): string
{
    $v = (string) $v;
    return ($v !== '' && strpbrk($v[0], "=+-@\t\r") !== false) ? "'" . $v : $v;
}

function kirim_csv(string $nama, array $header, iterable $rows): void
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nama . '"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
    fputcsv($out, $header, ';');
    foreach ($rows as $r) fputcsv($out, array_map('sel_csv', array_values($r)), ';');
    fclose($out);
    exit;
}
