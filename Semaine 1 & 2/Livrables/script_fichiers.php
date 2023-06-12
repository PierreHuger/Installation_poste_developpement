<?php

    class FileReader {

        private $file;
        private $patterns;
        private $data;

        public function __construct(string $fileName, string $mode) {
            $this->file = fopen($fileName, $mode)
                or die("E: Impossible d'ouvrir le fichier.");
            $this->patterns = [];
            $this->data = [];
        }

        public function addPattern(string $name, string $pattern) {
            $this->patterns[$name] = $pattern;
        }

        public function getPattern(string $name) {
            return $this->patterns[$name];
        }

        public function addData(string $dataName, string $key, $value, bool $withOcc = true) {
            $copy = strtolower($key);
            if($withOcc) {
                $key = $copy."_1";
                if(isset($this->data[$dataName])) {
                    foreach($this->data[$dataName] as $storedKey => $_) {
                        if(strpos($storedKey, $copy) !== false) {
                            $array = explode("_", $storedKey);
                            $nbOcc = end($array);
                            $key = $copy."_".(intval($nbOcc)+1);
                        }
                    }
                }
            }
            $this->data[$dataName][$key] = $value;
        }

        public function read() {
            return rtrim(fgets($this->file));
        }

        public function write($data) {
            fwrite($this->file, $data);
        }

        public function close() {
            fclose($this->file) 
                or die('E: Impossible de fermer le fichier.');
        }

        public function toDatFile(string $dataName) {
            $data = $this->data[$dataName];
            if(!isset($data))
                return die("E: impossible de trouver les données relatif à ".$dataName);
            $file = new FileReader($dataName.'.dat', "w");
            $file->write(json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
            $file->close();
        }

        public function match(string $line, string $patternName) {
            return preg_match('/'.$this->getPattern($patternName).'/i', $line);
        }

    }

    if(sizeof($argv) < 2) {
        echo("E: syntaxe: ./script_fichiers <nom_fichier>");
        exit();
    }

    $file = new FileReader($argv[1], "r");

    // on ajoute tous les patternes
    $file->addPattern('titre', '^titre');
    $file->addPattern('sous_titre', '^sous_titre');

    $file->addPattern('debut_texte', '^d[ée]but_texte$');
    $file->addPattern('fin_texte', '^fin_texte$');

    $file->addPattern('debut_credits', '^d[ée]but_cr[ée]dit(s?)$');
    $file->addPattern('fin_credits', '^fin_cr[ée]dit(s?)$');

    $file->addPattern('meilleurs', '^meilleur(s?):');

    $file->addPattern('debut_stats', '^d[ée]but_stat(s?)$');
    $file->addPattern('fin_stats', '^fin_stat(s?)$');


    // on lit le fichier  
    while($line = $file->read()) {

        // récupérer les titres
        if(
            $file->match($line, 'titre') ||
            $file->match($line, 'sous_titre')
        ) {
            [$key, $value] = explode("=", $line);
            $file->addData("texte", $key, $value);
        }

        // récupérer les textes
        if($file->match($line, 'debut_texte')) {
            $text = "";
            while ($line && !$file->match($line, 'fin_texte')) {
                $line = $file->read();
                if (!$file->match($line, 'fin_texte'))
                    $text .= trim($line);
            }
            $file->addData("texte", "texte", $text);
        }

        // récupérer les crédits
        if($file->match($line, 'debut_credits')) {
            $credits = [];
            while($line && !$file->match($line, 'fin_credits')) {
                $line = $file->read();
                if (!$file->match($line, 'fin_credits'))
                    array_push($credits, trim($line));
            }

            $file->addData("texte", "credits", $credits);
        }

        // récupérer les stats
        if($file->match($line, 'debut_stats')) {
            while ($line && !$file->match($line, 'fin_stats')) {
                $line = $file->read();
                if (!$file->match($line, 'fin_stats')) {
                    $stats = [];

                    [
                        $nom_produit,
                        $data
                    ] = explode(" ", $line);
                    [
                        $vente_trimestre,
                        $ca_trimestre,
                        $vente_annee_precedente,
                        $ca_annee_precedente,
                        $evolution_ca
                    ] = explode(",", $data);

                    $stats["nom_produit"] = $nom_produit;
                    $stats["vente_trimestre"] = $vente_trimestre;
                    $stats["ca_trimestre"] = intval($ca_trimestre);
                    $stats["vente_annee_precedente"] = intval($vente_annee_precedente);
                    $stats["ca_annee_precedente"] = intval($ca_annee_precedente);
                    $stats["evolution_ca"] = intval($evolution_ca);

                    $file->addData("tableau", "product", $stats);
                }
            }
        }

        // récupérer les meilleurs commerciaux
        if($file->match($line, 'meilleurs')) {
            $commerciaux = explode(":", $line)[1];
            $commerciaux = explode(",", $commerciaux);
            $liste_commerciaux = [];

            foreach($commerciaux as $commercial) {
                $commercial = explode("=", $commercial);
                $donnee_commercial = [];

                $nom = explode("/", $commercial[0])[1];
                $total_ventes = $commercial[1];
                
                $donnee_commercial["nom"] = $nom;
                $donnee_commercial["total_ventes"] = $total_ventes;
                array_push($liste_commerciaux, $donnee_commercial);
            }

            usort($liste_commerciaux, function ($a, $b) {
                return $a["total_ventes"] > $b["total_ventes"] ? -1 : 1;
            });
            $file->addData("comm", "meilleurs_commerciaux", $liste_commerciaux, false);
        }
    }

    $file->close();

    $file->toDatFile("comm");
    $file->toDatFile("tableau");
    $file->toDatFile("texte");
    echo("Fichiers com, tableau et texte créés !");