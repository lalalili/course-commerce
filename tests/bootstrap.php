<?php

// commerce-core / course-core 現在以 VCS 依賴裝進本套件自己的 vendor/,
// composer 產生的 autoloader 已涵蓋 composer.json 宣告的 PSR-4
// (Lalalili\CourseCommerce\ 與 ...\Tests\),不需要再手動註冊兄弟目錄。
return require __DIR__.'/../vendor/autoload.php';
