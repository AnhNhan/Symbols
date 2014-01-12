#!/usr/bin/php
<?php
require_once __DIR__ . '/../src/__init__.php';

use AnLang\Utils\Printers\ArrayPrinter;
use AnLang\Utils\Symbols\SymbolGenerator;

array_shift($argv);


// If not user-supplied, assume we are in vendor/bin dir 
$basePath = array_shift($argv) ?: dirname(dirname(__FILE__));

// First get all interesting .php files
$rawFiles = array();
try {
    $retVal = -1;
    chdir($basePath);
    exec('git ls-files --full-name -c', $rawFiles, $retVal);
    if ($retVal !== 0) {
        throw new Exception("Git failed!");
    }
    $files = sanitizeStringsFromPrefix(
        preg_grep("/\\.php$/", $rawFiles),
        'src/'
    );
} catch (Exception $exc) {
    println("Could not retrieved index files from Git.");
    println(
        "Falling back to file-based approach, which could index " .
        "unindexed files!"
    );
    $rawFiles = recursiveScanForDirectories($basePath, '\.php');
    $files = sanitizeStringsFromPrefix($rawFiles, $basePath);
}

$fileCount = count($files);

println("Analyzing $fileCount files...");

// Now begins the cool part :D
$symbolGenerator = new SymbolGenerator($basePath);
$skipped = array();
$filesToBeParsed = array();

foreach ($files as $file) {
    if (preg_match("/Test\\.php$/i", $file)) {
        $skipped[] = $file;
        continue;
    }
    $filesToBeParsed[] = $file;
}

println();
if ($skipped) {
    println("Skipping " . count($skipped) . " files:");
    echo "  - ";
    echo implode("\n  - ", $skipped);
    println();
    println();
}

$symbolGenerator->addFiles($filesToBeParsed);

$symbolGenerator->onFileTraverse(function ($fileName) {
    echo ".";
});

$symbolGenerator->run();

$symbolTree = $symbolGenerator->getTree();

println();
println();
println("Successfully analyzed $fileCount files!");
println("Found " . $symbolTree->symbolCount() . " symbols.");
println();
println("Writing to disk...");

$arrayPrinter = new ArrayPrinter();

ob_start();
echo <<<EOT
<?php
// -----------------------------------------------------------------------------
/**
 *  This file was generated by the SymbolGenerator(tm)
 *  Would be cool if you wouldn't edit it, as that would sure break things
 *
 *  To re-generate this file, run `php -f scripts/generate_symbol_list.php`
 *
 *  Thank you
 *  @love Anh Nhan Nguyen <anhnhan@outlook.com>
 *
 *  @generated
 */
// -----------------------------------------------------------------------------
EOT;

$symbolMapString = ob_get_clean();
$symbolMapString .= $arrayPrinter->printForFile($symbolTree->toSymbolMap());

file_put_contents(
    path("__symbol_map__.php"),
    $symbolMapString
);

println("Successfully wrote to disk!");
