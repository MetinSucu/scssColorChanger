# scssColorChanger

`scssColorChanger` sınıfı, SCSS dosyalarındaki renk değerlerini yönetmeye ve düzenlemeye yönelik bir araçtır. Bu sınıf, renk değerlerini değişkenlere dönüştürerek SCSS dosyalarınızı daha yönetilebilir hale getirebilir.

## Kurulum

Sınıfı kullanmak için aşağıdaki adımları takip edebilirsiniz:

1. `scssColorChanger` sınıfını projenize ekleyin.
2. Sınıfı kullanmaya başlamadan önce gerekli olan SCSS dosyalarınızı belirli bir klasörde topladığınızdan emin olun.

## Kullanım

```php
<?php

require_once 'class.scssColorChanger.php';

use Labkod\scssColorChanger;
// SCSS dosyalarının bulunduğu dizin.
$sccsScanDir = __DIR__ . "/assets/css";

// Renkleri çıkartacağı SCSS dosyası .
$outputFile = __DIR__ . "/assets/css/components/_colors.scss";

// Taranan dizinde hariç bırakılacak klasörler ve SCSS dosyaları.
$excludePath = [
    "_breakpoints.scss",
    "bootstrap",
    "_font.scss",
    "_mixin.scss",
    "style.scss",
    "mobile.scss",
];

// Sınıfı başlatın
$generator = new scssColorChanger($outputFile, $sccsScanDir, $excludePath);

// İşlemleri başlatmadan önce değişiklik yapılacak olan SCSS dosyalarının  listesini inceleyin.
print_r($generator->getFileList());

// SCSS Renk Değiştirme İşlemini Başlatın.
$generator->init();
// Tamamlandı.


?>

```
## Metodlar
##` init()`
`scssColorChanger`'ı başlatır ve işlemi başlatır. Tüm SCSS dosyalarını işler, renk değişkenlerini oluşturur ve çıkış dosyasını günceller.
##` getFileList()`
İlgili taranan dizindeki SCSS dosyalarının bir listesini döndürür.
## Diğer Metodlar

- **`readOutputFile($outputFile)`**: Çıkış dosyasını okur ve mevcut renk değişkenlerini belirler.
-  **`scanDirectory($directory)`**: Verilen dizindeki SCSS dosyalarını tarar.
-  **`readFile($filePath)`**: Belirtilen dosyayı okur ve içeriği döndürür.
-  **`getCSSRootContent($cssContent)`**: CSS içeriğindeki :root bloğunu bulur ve içeriğini döndürür.
-  **`extractCssVariables($scssContent)`**: SCSS içeriğinden CSS değişkenlerini çıkarır.
-  **`processScssFile($filePath)`**: Belirtilen SCSS dosyasını işler, renkleri bulur ve yeni değişken adları oluşturur.
-  **`findColors($scssContent)`**: SCSS içeriğinden renkleri bulur ve sınıfa ekler.
-  **`findColors($scssContent)`**: SCSS içeriğinden renkleri bulur ve sınıfa ekler.
-  **`generateColorsVariableName()`** : Bulunan renklere benzersiz değişken adları oluşturur.
-  **`generateVariableName()`**: Yeni değişken adı oluşturur.
-  **`updateSCSSContent($scssContent)`**: SCSS içeriğindeki renk değerlerini oluşturulan değişken adları ile günceller.
-  **`generateOutputFile($outputFile)`**: Çıkış dosyasını günceller ve yeni renk değişkenlerini ekler.
-  **`sortColorsByHue($colors)`**: Renkleri tonlarına göre sıralar.
-  **`updateRootContent($cssContent, $newContent)`**: CSS içeriğindeki :root bloğunu günceller ve yeni renk değişkenlerini ekler.
-  **`hexToRgb($hex)`**: HEX renk kodunu RGB değerlerine çevirir.
-  **`detectColorFormat($colorCode)`**: Verilen renk kodunun formatını belirler.
-  **`rgbToHsl($rgb)`**: RGB renk değerini HSL (ton, doygunluk, ışık) değerine çevirir.
-  **`createWriteFile($file, $content)`**: Belirtilen dosyayı oluşturur ve içeriği yazar.
-  **`compareColorsByLuminance($color1, $color2)`**: Renkleri parlaklık (luminance) değerlerine göre karşılaştırır.
