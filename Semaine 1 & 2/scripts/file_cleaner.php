<?php

    class FileReader {

        private $file;
        private $patterns;
        private $data;

        public function __construct(string $fileName, string $mode) {
            $this->file = fopen($fileName, $mode)
                or die("[ERREUR] Impossible d'ouvrir le fichier.");
            $this->patterns = [];
            $this->data = [];
        }

        public function addPattern(string $name, string $pattern) {
            $this->patterns[$name] = $pattern;
        }

        public function getPattern(string $name) {
            return $this->patterns[$name];
        }

        public function addData(string $key, $value) {
            $copy = strtolower($key);
            $key = $copy."_1";
            foreach($this->data as $storedKey => $_) {
                if(strpos($storedKey, $copy) !== false) {
                    $array = explode("_", $storedKey);
                    $nbOcc = end($array);
                    $key = $copy."_".(intval($nbOcc)+1);
                }
            }
            $this->data[$key] = $value;
        }

        public function read() {
            return rtrim(fgets($this->file));
        }

        public function write($data) {
            fwrite($this->file, $data);
        }

        public function close() {
            fclose($this->file) or die('[ERREUR] Impossible de fermer le fichier.');
        }

        public function toDat(string $outputName) {
            $file = new FileReader($outputName.'.dat', "w");
            $file->write(json_encode($this->data));
            $file->close();
        }

        public function match(string $line, string $patternName) {
            return preg_match('/'.$this->getPattern($patternName).'/i', $line);
        }

    }

    $file = new FileReader("Bretagne.txt", "r");

    $file->addPattern('titre', '^titre');
    $file->addPattern('sous_titre', '^sous_titre');

    $file->addPattern('debut_texte', '^d[ée]but_texte$');
    $file->addPattern('fin_texte', '^fin_texte$');

    $file->addPattern('debut_credits', '^d[ée]but_cr[ée]dit(s?)$');
    $file->addPattern('fin_credits', '^fin_cr[ée]dit(s?)$');

    $SEPARATOR = "=";
    while($line = $file->read()) {
        if(
            $file->match($line, 'titre') ||
            $file->match($line, 'sous_titre')
        ) {
            [$key, $value] = explode($SEPARATOR, $line);
            $file->addData($key, $value);
        }

        // récupérer les textes
        if($file->match($line, 'debut_texte')) {
            $text = "";
            while ($line && !$file->match($line, 'fin_texte')) {
                $line = $file->read();
                if (!$file->match($line, 'fin_texte'))
                    $text .= trim($line);
            }
            $file->addData("texte", $text);
        }

        // récupérer les crédits
        if($file->match($line, 'debut_credits')) {
            $credits = [];
            while($line && !$file->match($line, 'fin_credits')) {
                $line = $file->read();
                if (!$file->match($line, 'fin_credits'))
                    array_push($credits, trim($line));
            }

            $file->addData("credits", $credits);
        }
    }
    $file->close();
    $file->toDat("out");
