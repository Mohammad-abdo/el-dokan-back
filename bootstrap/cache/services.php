<?php return array (
  'providers' => 
  array (
    0 => 'Barryvdh\\DomPDF\\ServiceProvider',
    1 => 'Intervention\\Image\\ImageServiceProvider',
    2 => 'Laravel\\Reverb\\ApplicationManagerServiceProvider',
    3 => 'Laravel\\Reverb\\ReverbServiceProvider',
    4 => 'Laravel\\Sail\\SailServiceProvider',
    5 => 'Laravel\\Sanctum\\SanctumServiceProvider',
    6 => 'Laravel\\Tinker\\TinkerServiceProvider',
    7 => 'Maatwebsite\\Excel\\ExcelServiceProvider',
    8 => 'Carbon\\Laravel\\ServiceProvider',
    9 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
    10 => 'Termwind\\Laravel\\TermwindServiceProvider',
    11 => 'SimpleSoftwareIO\\QrCode\\QrCodeServiceProvider',
    12 => 'Spatie\\LaravelIgnition\\IgnitionServiceProvider',
    13 => 'Spatie\\Permission\\PermissionServiceProvider',
  ),
  'eager' => 
  array (
    0 => 'Barryvdh\\DomPDF\\ServiceProvider',
    1 => 'Intervention\\Image\\ImageServiceProvider',
    2 => 'Laravel\\Reverb\\ReverbServiceProvider',
    3 => 'Laravel\\Sanctum\\SanctumServiceProvider',
    4 => 'Maatwebsite\\Excel\\ExcelServiceProvider',
    5 => 'Carbon\\Laravel\\ServiceProvider',
    6 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
    7 => 'Termwind\\Laravel\\TermwindServiceProvider',
    8 => 'SimpleSoftwareIO\\QrCode\\QrCodeServiceProvider',
    9 => 'Spatie\\LaravelIgnition\\IgnitionServiceProvider',
    10 => 'Spatie\\Permission\\PermissionServiceProvider',
  ),
  'deferred' => 
  array (
    'Laravel\\Reverb\\ApplicationManager' => 'Laravel\\Reverb\\ApplicationManagerServiceProvider',
    'Laravel\\Reverb\\Contracts\\ApplicationProvider' => 'Laravel\\Reverb\\ApplicationManagerServiceProvider',
    'Laravel\\Sail\\Console\\InstallCommand' => 'Laravel\\Sail\\SailServiceProvider',
    'Laravel\\Sail\\Console\\PublishCommand' => 'Laravel\\Sail\\SailServiceProvider',
    'command.tinker' => 'Laravel\\Tinker\\TinkerServiceProvider',
  ),
  'when' => 
  array (
    'Laravel\\Reverb\\ApplicationManagerServiceProvider' => 
    array (
    ),
    'Laravel\\Sail\\SailServiceProvider' => 
    array (
    ),
    'Laravel\\Tinker\\TinkerServiceProvider' => 
    array (
    ),
  ),
);