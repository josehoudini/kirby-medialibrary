<?php

use Kirby\Cms\App as Kirby;
use Kirby\Cms\File;

Kirby::plugin('josehoudini/media-library', [

	'areas' => [
		'media-library' => fn(Kirby $kirby) => [

			'label' => 'Files',
			'icon'  => 'folder',
			'menu'  => true,
			'link'  => 'media-library',

			'views' => [
				mediaView($kirby, 'media-library', 'images'),
				mediaView($kirby, 'media-library/images', 'images'),
				mediaView($kirby, 'media-library/videos', 'videos'),
				mediaView($kirby, 'media-library/other', 'other'),
			],
		],
	],
]);

/* VIEWS */
function mediaView(Kirby $kirby, string $pattern, string $tab): array
{
	return [
		'pattern' => $pattern,
		'action'  => fn() => [
			'component' => 'sitefiles',
			'title'     => ucfirst($tab),
			'props'     => [
				'tab'    => $tab,
				'images' => $tab === 'images' ? mediaFiles($kirby, 'image') : [],
				'videos' => $tab === 'videos' ? mediaFiles($kirby, 'video') : [],
				'other'	 => $tab === 'other' ? mediaFiles($kirby, 'other') : []
			],
		],
	];
}

/* FILE MAPPING */
function mediaFiles(Kirby $kirby, string $type)
{
	$files = $kirby->site()->index()->files();

	if ($type === 'image' || $type === 'video') {
		$files = $files->filterBy('type', $type);
	} elseif ($type === 'other') {
		$files = $files->filter(fn($file) => !in_array($file->type(), ['image', 'video']));
	}

	return $files->map(fn(File $file) => mapMediaFile($file, $file->type()))->values();
}

/* NORMALIZE DATA */
function mapMediaFile(File $file, string $type): array
{
	return [
		'id'    => $file->id(),
		'text'  => $file->filename(),
		'image' => mediaThumb($file, $type),
		'link'  => $file->panel()->url(true),
		'info'  => formatSize($file->size()),
	];
}

/* ITEM THUMBNAILS */
function mediaThumb(File $file, string $type): array
{
	$thumb = ['width' => 96, 'height' => 96, 'crop' => true];
	$srcset = [
		'1x' => $thumb,
		'2x' => ['width' => 192, 'height' => 192, 'crop' => true],
	];

	if ($type === 'image') {
		return [
			'src'    => $file->thumb($thumb)->url(),
			'srcset' => $file->srcset($srcset),
		];
	}

	if ($poster = $file->poster()->toFile()) {
		return [
			'src'    => $poster->thumb($thumb)->url(),
			'srcset' => $poster->srcset($srcset),
		];
	}

	return $type === 'video' ? ['icon' => 'video'] : ['icon' => 'file'];
}

/* MAKE FILE SIZE READABLE */
function formatSize(int $bytes): string
{
	$kb = $bytes / 1024;
	
	if ($kb < 1024) {
		return number_format($kb, 2) . ' KB';
	}
	
	return number_format($kb / 1024, 2) . ' MB';
}
