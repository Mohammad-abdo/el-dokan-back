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
    7 => 'Carbon\\Laravel\\ServiceProvider',
    8 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
    9 => 'Termwind\\Laravel\\TermwindServiceProvider',
    10 => 'SimpleSoftwareIO\\QrCode\\QrCodeServiceProvider',
    11 => 'Spatie\\LaravelIgnition\\IgnitionServiceProvider',
    12 => 'Spatie\\Permission\\PermissionServiceProvider',
  ),
  'eager' => 
  array (
    0 => 'Barryvdh\\DomPDF\\ServiceProvider',
    1 => 'Intervention\\Image\\ImageServiceProvider',
    2 => 'Laravel\\Reverb\\ReverbServiceProvider',
    3 => 'Laravel\\Sanctum\\SanctumServiceProvider',
    4 => 'Carbon\\Laravel\\ServiceProvider',
    5 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
    6 => 'Termwind\\Laravel\\TermwindServiceProvider',
    7 => 'SimpleSoftwareIO\\QrCode\\QrCodeServiceProvider',
    8 => 'Spatie\\LaravelIgnition\\IgnitionServiceProvider',
    9 => 'Spatie\\Permission\\PermissionServiceProvider',
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