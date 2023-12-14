<?php
namespace Labkod;

class scssColorChanger
{

    private $variables = [];
    private $foundColors = [];
    private $alreadyDefinedVariables = [];
    private $variableCounter = 1;
    private $oldOutputContent = "";
    private $outputVariables = [];
    private $beforeGeneratedVariables = [];
    private $variableText = "colorVar";
    private $fileList = [];
    private $outputFile;
    private $scanDirectory;
    private $excludePath = [];

    public function __construct($outputFile, $scanDirectory, $excludePath = [])
    {

        $this->outputFile = $outputFile;
        $this->scanDirectory = rtrim($scanDirectory, "/");

        foreach ($excludePath as $path) {
            $this->excludePath[] = trim($path);
        }
        $excludeOutputFile = ltrim(str_replace($this->scanDirectory, "", $this->outputFile), "/");
        $this->excludePath[] = $excludeOutputFile;
        $this->fileList = $this->scanDirectory($this->scanDirectory);

    }

    private function scanDirectory($directory)
    {


        $files = [];

        $items = scandir($directory);
        foreach ($items as $item) {

            if ($item == '.' || $item == '..') {
                continue;
            }
            $isExclude = false;
            $path = $directory . '/' . $item;
            foreach ($this->excludePath as $i => $excludePath) {
                $excludeFullPath = $this->scanDirectory . "/" . $excludePath;
                if ($excludeFullPath == $path) {
                    $isExclude = true;
                    unset($this->excludePath[$i]);
                    continue;
                }

            }
            if (!$isExclude) {
                if (is_dir($path)) {
                    $files = array_merge($files, $this->scanDirectory($path));
                } else {
                    if (pathinfo($path, PATHINFO_EXTENSION) == 'scss') {
                        $files[] = $path;
                    }
                }
            }
        }

        return $files;
    }

    public function getFileList()
    {
        return $this->fileList;
    }

    public function init()
    {
        $this->readOutputFile($this->outputFile);

        foreach ($this->fileList as $i => $filePath) {

            $this->processScssFile($filePath);
            echo "\n--------";

        }
        $this->generateOutputFile($this->outputFile);
    }

    private function readOutputFile($outputFile)
    {
        if (file_exists($outputFile)) {
            $outputContent = $this->readFile($outputFile);
            if ($outputContent) {
                $rootContent = $this->getCSSRootConten($outputContent);
                $this->oldOutputContent = $outputContent;
                $this->outputVariables = $this->extractCssVariables($rootContent);

                foreach ($this->outputVariables as $variableName => $color) {
                    $pos = strpos($variableName, $this->variableText);
                    if ($pos !== false) {
                        $this->beforeGeneratedVariables[$variableName] = $color;
                    }
                }

            }
        }

    }

    private function readFile($filePath)
    {
        if (file_exists($filePath) && filesize($filePath) > 0) {
            $fileHandle = fopen($filePath, 'r');
            if (!$fileHandle) {
                echo "Unable to open file:$filePath";
                return false;
            }
            $scssContent = fread($fileHandle, filesize($filePath));
            fclose($fileHandle);
            return $scssContent;
        }
    }

    private function getCSSRootConten($cssContent)
    {
        preg_match('/:root\s*{([^}]+)}/', $cssContent, $matches);

        if (!empty($matches[1])) {
            $rootContent = trim($matches[1]);
            return $rootContent;
        }

        return '';
    }

    private function extractCssVariables($scssContent)
    {
        $variables = [];
        preg_match_all('/--([a-zA-Z0-9_-]+)\s*:\s*([^;]+);/', $scssContent, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $variableName = trim($match[1]);
            $variableValue = trim($match[2]);
            $variables[$variableName] = $variableValue;
        }

        return $variables;
    }

    private function processScssFile($filePath)
    {
        $scssContent = $this->readFile($filePath);
        $this->findColors($scssContent);
        $this->generateColorsVariableName();
        $newSCSSContent = $this->updateSCSSContent($scssContent);
        echo $filePath . "\n";
         echo $newSCSSContent;
        $this->createWriteFile($filePath, $newSCSSContent);
    }

    private function findColors($scssContent)
    {
        if (!empty($scssContent)) {
            preg_match_all('/#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})/', $scssContent, $matchesHex);
            preg_match_all('/rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)/', $scssContent, $matchesRgb);
            preg_match_all('/rgba\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*([01]?(\.\d+)?)\s*\)/', $scssContent, $matchesRgba);
            preg_match_all('/--([a-zA-Z0-9_-]+)/', $scssContent, $cssVariables);
            $this->alreadyDefinedVariables = array_merge($this->alreadyDefinedVariables, $cssVariables[1]);
            $this->foundColors = array_merge($this->foundColors, $matchesHex[0], $matchesRgb[0], $matchesRgba[0]);

        }

    }

    private function generateColorsVariableName()
    {

        foreach ($this->foundColors as $color) {

            if (!in_array($color, $this->variables) && !in_array($color, $this->beforeGeneratedVariables)) {
                $variableName = $this->generateVariableName();
                $this->variables[$variableName] = $color;
            }
        }


    }

    private function generateVariableName()
    {

        $variableName = $this->variableText . $this->variableCounter;
        if (isset($this->outputVariables[$variableName]) || isset($this->variables[$variableName]) || in_array($variableName, $this->alreadyDefinedVariables)) {
            $this->variableCounter++;
            $variableName = $this->generateVariableName();
        }
        return $variableName;
    }

    private function updateSCSSContent($scssContent)
    {

        if (!empty($scssContent)) {
            foreach ($this->variables as $variableName => $color) {
                $scssVariable = "var(--" . $variableName . ")";
                $scssContent = str_replace($color, $scssVariable, $scssContent);
            }
            foreach ($this->beforeGeneratedVariables as $variableName => $color) {
                $scssVariable = "var(--" . $variableName . ")";
                $scssContent = str_replace($color, $scssVariable, $scssContent);
            }
        }
        return $scssContent;


    }

    private function generateOutputFile($outputFile)
    {

        $outputContent = "";

        $this->variables = $this->sortColorsByHue($this->variables);



        foreach ($this->variables as $variableName => $color) {

            if (in_array($color, $this->outputVariables)) {
                $matchingKeys = array_keys($this->outputVariables, $color);
                $variableLine = "--" . $variableName . ":" . $color . "; // --" . $matchingKeys[0] . "\n";
            } else {
                $variableLine = "--" . $variableName . ":" . $color . ";\n";
            }
            $outputContent .= $variableLine;
        }

        $outputContent = $this->updateRootContent($this->oldOutputContent, $outputContent);

         $this->createWriteFile($outputFile, $outputContent);

    }

    private function sortColorsByHue(array $colors)
    {
        uasort($colors, [$this, 'compareColorsByLuminance']);
        return $colors;
    }

    private function updateRootContent($cssContent, $newContent)
    {
        preg_match('/:root\s*{([^}]+)}/', $cssContent, $matches);

        if (!empty($matches[1])) {
            $rootContent = trim($matches[1]);
            $rootContent .= "\n  $newContent";
            $newRootBlock = ":root {\n$rootContent\n}";
            $cssContent = preg_replace('/:root\s*{([^}]+)}/', $newRootBlock, $cssContent);
        } else {
            $cssContent = "\n:root {\n  $newContent \n}\n" . $cssContent;
        }

        return $cssContent;
    }

    private function hexToRgb($hex)
    {
        $format = $this->detectColorFormat($hex);

        if ($format == "hex") {
            $hexCode = str_replace('#', '', $hex);


            if (strlen($hexCode) == 6) {
                list($r, $g, $b) = str_split($hexCode, 2);
            } elseif (strlen($hexCode) == 3) {
                list($r, $g, $b) = str_split($hexCode . $hexCode, 2);
            } else {

                return false;
            }


            $r = hexdec($r);
            $g = hexdec($g);
            $b = hexdec($b);

            return $this->rgbToHsl(['r' => $r, 'g' => $g, 'b' => $b]);
        } elseif ($format == "rgb") {
            $rgbaPattern = '/^rgba\((\d{1,3}), (\d{1,3}), (\d{1,3}), (0(\.\d+)?|1(\.0+)?)\)$/';
            if (preg_match($rgbaPattern, $hex, $matches)) {
                $red = (int)$matches[1];
                $green = (int)$matches[2];
                $blue = (int)$matches[3];
                $alpha = (float)$matches[4];
                $alpha = $alpha;
                return $this->rgbToHsl(['r' => $red, 'g' => $green, 'b' => $blue, 'a' => $alpha]);

            }
        }

    }

    function detectColorFormat($colorCode)
    {
        $hexPattern = '/^#?([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/';
        $rgbPattern = '/^rgba?\((\d{1,3}), (\d{1,3}), (\d{1,3})(, (\d+(\.\d+)?))?\)$/';
        if (preg_match($hexPattern, $colorCode)) {
            return 'hex';
        } elseif (preg_match($rgbPattern, $colorCode)) {
            return 'rgb';
        } else {
            return 'unknown';
        }
    }

    private function rgbToHsl($rgb)
    {

        $colorHSL = $rgb['r'] + $rgb['g'] + $rgb['b'];
        if(isset($rgb['a'])){
            $colorHSL*=$rgb['a'];
        }


        return $colorHSL;
    }

    private function createWriteFile($file, $content)
    {

        $myfile = fopen($file, "w");
        if ($myfile) {
            fwrite($myfile, $content);
            fclose($myfile);
            return $file;
        } else {
            return false;
        }
    }

    private function compareColorsByLuminance($color1, $color2)
    {

        $hsl1 = $this->hexToRgb($color1);
        $hsl2 = $this->hexToRgb($color2);

        return $hsl1 <=> $hsl2;
    }

}

?>
