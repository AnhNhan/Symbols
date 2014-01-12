Symbols
=======

Symbol discovery and autoloading (and some more)

Function autoloading   
====================
  
If you don't need autoloading support at PHP-Level (like all of those guys who don't complain about function autoloading), you can always ~~write your own~~ use this symbol discovery and loading tool.
  
It generates a [`__symbol_map__.php`](https://github.com/AnhNhan/AnLang/blob/master/src/__symbol_map__.php) that you use like   

    use AnLang\Utils\Symbols\SymbolLoader;
  
    // __symbol_map__.php is in the same folder
    SymbolLoader::setStaticRootDir(__DIR__);
    $symbolLoader = SymbolLoader::getInstance();
    $symbolLoader->register();  
    // Autoload all functions in this project
    $symbolLoader->loadAllFunctions();    
    
There are a few more utilities in the Symbol loader (it can do class-autoloading, too), as well as knows about the class hierarchy:  
  
    // Never, ever forget to register your new console commands again :)
    $commands = SymbolLoader::getInstance()
        ->getConcreteClassesThatDeriveFromThisOne('AnhNhan\Project\Console\AbstractCommand');

There's autoloader support, too, but too lazy to unpack.