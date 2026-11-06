<?php
/** Tukar urutan baris dengan tetangganya (naik/turun). Tabel & kolom dari daftar putih. */
function geser_urutan(string $tabel, int $id, string $arah): void
{
    if (!in_array($tabel, ['proyek', 'teknologi', 'layanan', 'testimoni'], true)) return;
    db()->beginTransaction();
    // Rapikan dulu agar urutan selalu 1..n tanpa angka kembar
    $ids = q("SELECT id FROM `$tabel` ORDER BY urutan, id")->fetchAll(PDO::FETCH_COLUMN);
    $pos = array_search($id, array_map('intval', $ids), true);
    $tuju = $arah === 'naik' ? $pos - 1 : $pos + 1;
    if ($pos !== false && isset($ids[$tuju])) [$ids[$pos], $ids[$tuju]] = [$ids[$tuju], $ids[$pos]];
    $st = db()->prepare("UPDATE `$tabel` SET urutan = ? WHERE id = ?");
    foreach ($ids as $i => $rid) $st->execute([$i + 1, $rid]);
    db()->commit();
}

function urutan_baru(string $tabel): int
{
    return (int) q("SELECT COALESCE(MAX(urutan), 0) + 1 FROM `$tabel`")->fetchColumn();
}
