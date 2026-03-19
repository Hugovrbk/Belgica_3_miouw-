<?php
/**
 * Calcule le classement du championnat depuis la base de données.
 * Retourne un tableau trié par points, différence de buts, buts pour.
 * Requiert que $pdo soit déjà défini.
 */
function calculerClassement($pdo) {
    $classement = [];
    try {
        $equipes_stmt = $pdo->query("SELECT nom FROM equipes ORDER BY nom ASC");
        $toutes_equipes = $equipes_stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($toutes_equipes as $eq) {
            $classement[$eq] = [
                'pts' => 0, 'j' => 0, 'v' => 0, 'n' => 0, 'd' => 0,
                'bp' => 0, 'bc' => 0, 'diff' => 0
            ];
        }

        $resultats_stmt = $pdo->query("SELECT * FROM resultats ORDER BY journee ASC");
        $tous_resultats = $resultats_stmt->fetchAll();

        foreach ($tous_resultats as $r) {
            $dom = $r['equipe_domicile'];
            $ext = $r['equipe_exterieur'];
            $bd  = $r['buts_domicile'];
            $be  = $r['buts_exterieur'];

            if (!isset($classement[$dom])) {
                $classement[$dom] = ['pts'=>0,'j'=>0,'v'=>0,'n'=>0,'d'=>0,'bp'=>0,'bc'=>0,'diff'=>0];
            }
            if (!isset($classement[$ext])) {
                $classement[$ext] = ['pts'=>0,'j'=>0,'v'=>0,'n'=>0,'d'=>0,'bp'=>0,'bc'=>0,'diff'=>0];
            }

            $classement[$dom]['j']++;
            $classement[$ext]['j']++;
            $classement[$dom]['bp'] += $bd;
            $classement[$dom]['bc'] += $be;
            $classement[$ext]['bp'] += $be;
            $classement[$ext]['bc'] += $bd;

            if ($bd > $be) {
                $classement[$dom]['v']++;   $classement[$dom]['pts'] += 3;
                $classement[$ext]['d']++;
            } elseif ($bd === $be) {
                $classement[$dom]['n']++;   $classement[$dom]['pts']++;
                $classement[$ext]['n']++;   $classement[$ext]['pts']++;
            } else {
                $classement[$ext]['v']++;   $classement[$ext]['pts'] += 3;
                $classement[$dom]['d']++;
            }
        }

        foreach ($classement as $nom => &$stats) {
            $stats['diff'] = $stats['bp'] - $stats['bc'];
        }
        unset($stats);

        uasort($classement, function($a, $b) {
            if ($b['pts'] !== $a['pts']) return $b['pts'] - $a['pts'];
            if ($b['diff'] !== $a['diff']) return $b['diff'] - $a['diff'];
            return $b['bp'] - $a['bp'];
        });

    } catch (PDOException $e) {
        $classement = [];
    }
    return $classement;
}
