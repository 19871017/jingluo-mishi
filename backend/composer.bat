@echo OFF
:: in case DelayedExpansion is on and a path contains ! 
setlocal DISABLEDELAYEDEXPANSION
"D:\phpstudy_pro\Extensions\php\php8.0.2nts\php.exe" "D:\phpstudy_pro\Extensions\composer2.5.8\composer.phar" %*