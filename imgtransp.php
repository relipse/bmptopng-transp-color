<?php
$shortopts = 'h';
$shortopts .= 'f:';
$shortopts .= 'd:';

$longopts = [
    'file:',   //file to convert .bmp
    'dir:',    //directory to convert all *.bmps
    'help',
];

$opts = getopt($shortopts, $longopts);

$helpstr = <<<EOT
Convert bmp files to png files and then set the transparency color to the pixel from the
top left (0,0) position.
php $argv[0]

Convert .bmp file to .png and convert them with transparency from top left pixel color.
Usage: php $argv[0] -f=my.bmp

Find all *.bmp files in current directory and convert them to png with transparency
from top left pixel color.
php $argv[0] --dir=.

 -f, --file        File specified to convert from bmp to png with transparency
 -d, --dir         Directory of *.bmp files to convert to png with transparency
 -h, --help        Show this help message.
EOT;

if (isset($opts['help']) || isset($opts['h'])){
    die($helpstr);
}

$file = $opts['file'] ?? $opts['f'] ?? null;
$dir = $opts['dir'] ?? $opts['d'] ?? null;

if (empty($file) && empty($dir)){
    echo "*** No --file or --dir specified ***\n";
    die($helpstr);
}

if (!empty($file)){
    if (strpos($file, '.bmp') === false){
        echo "*** Must be a .bmp file: $file ***\n";
        die($helpstr);
    }
    if (!file_exists($file)){
        echo "*** File does not exist: $file ***\n";
        die($helpstr);
    }
    $pngfile = convert_from_bmp_to_png($file);
    echo "Converted to png file: $pngfile\n";
    make_top_left_pixel_transparent_color($pngfile);
}else if (!empty($dir)){
    if (!file_exists($dir)){
        $dir2 = __DIR__.'/'.$dir;
        if (!file_exists($dir2)) {
            echo "*** Dir does not exist: $dir ***\n";
            die($helpstr);
        }else{
            $dir = $dir2;
        }
    }

    if (!is_dir($dir)){
        echo "*** Not a valid directory: $dir ***\n";
        die($helpstr);
    }
    if ($dir[strlen($dir)-1] == '/'){
        $dir = substr($dir, 0, strlen($dir)-1);
    }
    if ($dir == '.'){
        $dir = __DIR__;
    }
    $bmpfiles = glob($dir.'/*.bmp');
    foreach($bmpfiles as $bmpfile){
        echo $bmpfile."\n";
        $pngfile = convert_from_bmp_to_png($bmpfile);
        echo "Converted to: $pngfile\n";
        make_top_left_pixel_transparent_color($pngfile);
        echo "Top Left Pixel Color made transparent.\n";
    }
}

/**
 * Convert bmp to png
 * @param string $bmpfile
 * @return string
 */
function convert_from_bmp_to_png(string $bmpfile){
    $pngfile = str_replace('.bmp','.png', $bmpfile);
// Load the BMP file
    $im = imagecreatefrombmp($bmpfile);
// Convert it to a PNG file with default settings
    imagepng($im, $pngfile);
    imagedestroy($im);
    return $pngfile;
}

/**
 * Does what it says, sets png file transparent color to pixel from top left (x,y)
 * @param string $pngfile
 * @param int $x
 * @param int $y
 * @return void
 */
function make_top_left_pixel_transparent_color(string $pngfile, int $x = 0, int $y = 0){
    $resource = imagecreatefrompng($pngfile);
    $color = imagecolorat($resource, $x, $y);
    imagecolortransparent($resource,$color);
    imagepng($resource, $pngfile);
    imagedestroy($resource);
}
