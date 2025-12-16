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
			'views' => mediaViews($kirby),
		],
	],
]);

// FILE VIEWS
function mediaViews(Kirby $kirby): array
{
	$tabs = [
		'media-library'         => 'images',
		'media-library/images'  => 'images',
		'media-library/videos'  => 'videos',
		'media-library/other'   => 'other',
	];

	$files = collectMediaFiles($kirby);

	return array_map(
		fn($pattern, $tab) => mediaView($pattern, $tab, $files),
		array_keys($tabs),
		$tabs
	);
}

function mediaView(string $pattern, string $tab, array $files): array
{
	return [
		'pattern' => $pattern,
		'action'  => fn() => [
			'component' => 'sitefiles',
			'title'     => ucfirst($tab),
			'props'     => [
				'tab'    => $tab,
				...$files,
			],
		],
	];
}

/* FILE MAPPING */
function collectMediaFiles(Kirby $kirby): array
{
	$files = $kirby->site()
		->files()
		->add($kirby->site()->index()->files());

	$filters = [
		'images' => fn($f) => $f->type() === 'image',
		'videos' => fn($f) => $f->type() === 'video',
		'other'  => fn($f) => !in_array($f->type(), ['image', 'video'], true),
	];

	return array_map(
		fn($filter) => $files->filter($filter)->map(fn(File $file) => mapMediaFile($file))->values(),
		$filters
	);
}

/* NORMALIZE DATA */
function mapMediaFile(File $file): array
{
	$type = $file->type();
	$dimensions = formatDimensions($file);

	return [
		'id'    => $file->id(),
		'text'  => $file->filename(),
		'image' => mediaThumb($file),
		'link'  => $file->panel()->url(true),
		'info'  => ($dimensions ?  $dimensions . '<br>' : '').
		formatSize($file->size())
	];
}

/* ITEM THUMBNAILS */
function mediaThumb(File $file): array
{
	$type = $file->type();

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

	if ($type === 'video' && ($poster = $file->poster()->toFile())) {
		return [
			'src'    => $poster->thumb($thumb)->url(),
			'srcset' => $poster->srcset($srcset),
		];
	}

	return ['icon' => $type === 'video' ? 'video' : 'file'];
}

/* FORMAT SIZE */
function formatSize(int $bytes): string
{
	return number_format($bytes / 1024 / 1024, 2) . ' MB';
}

/* FORMAT DIMENSIONS */
function formatDimensions(File $file): string
{
	if ($file->type() !== 'image') {
		return '';
	}

	$dimensions = $file->dimensions();

	return $dimensions->width() && $dimensions->height()
		? $dimensions->width() . ' × ' . $dimensions->height() . ' px'
		: '';
}