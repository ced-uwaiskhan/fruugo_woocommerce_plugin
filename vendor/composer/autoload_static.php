<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd4b3a1ddd35c2409568644f1e139406f {

	public static $prefixLengthsPsr4 = array(
		'B' =>
		array(
			'Box\\Spout\\' => 10,
		),
	);

	public static $prefixDirsPsr4 = array(
		'Box\\Spout\\' =>
		array(
			0 => __DIR__ . '/..' . '/box/spout/src/Spout',
		),
	);

	public static $classMap = array(
		'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
	);

	public static function getInitializer( ClassLoader $loader ) {
		return \Closure::bind(
			function () use ( $loader ) {
				$loader->prefixLengthsPsr4 = ComposerStaticInitd4b3a1ddd35c2409568644f1e139406f::$prefixLengthsPsr4;
				$loader->prefixDirsPsr4    = ComposerStaticInitd4b3a1ddd35c2409568644f1e139406f::$prefixDirsPsr4;
				$loader->classMap          = ComposerStaticInitd4b3a1ddd35c2409568644f1e139406f::$classMap;

			},
			null,
			ClassLoader::class
		);
	}
}
