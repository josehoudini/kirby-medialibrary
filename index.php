<?php

use Kirby\Cms\App as Kirby;
use Kirby\Cms\File;
use Kirby\Cache\Cache;

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
	'api' => [
		'routes' => [
			[
				'pattern' => 'media-library/files',
				'method'  => 'GET',
				'action'  => function () {
					$tab = get('tab', 'images');
					$page = max(1, (int) get('page', 1));
					$limit = min(100, max(1, (int) get('limit', 30)));

					return getMediaFiles($tab, $page, $limit);
				}
			],
			[
				'pattern' => 'media-library/stats',
				'method'  => 'GET',
				'action'  => function () {
					return getMediaStats();
				}
			]
		]
	],
	'hooks' => [
		'file.create:after' => fn() => clearMediaCache(),
		'file.update:after' => fn() => clearMediaCache(),
		'file.delete:after' => fn() => clearMediaCache(),
		'file.replace:after' => fn() => clearMediaCache(),
		'file.changeName:after' => fn() => clearMediaCache(),
		'page.create:after' => fn() => clearMediaCache(),
		'page.delete:after' => fn() => clearMediaCache(),
	],
]);

// CACHE HELPER
function getMediaCache(): ?Cache
{
	return kirby()->cache('josehoudini.media-library');
}

function clearMediaCache(): void
{
	getMediaCache()?->flush();
}

// FILE VIEWS
function mediaViews(Kirby $kirby): array
{
	$tabs = [
		'media-library'         => 'images',
		'media-library/images'  => 'images',
		'media-library/videos'  => 'videos',
		'media-library/documents' => 'documents',
		'media-library/other'   => 'other',
	];

	return array_map(
		fn($pattern, $tab) => mediaView($pattern, $tab),
		array_keys($tabs),
		$tabs
	);
}

function mediaView(string $pattern, string $tab): array
{
	return [
		'pattern' => $pattern,
		'action'  => fn() => [
			'component' => 'sitefiles',
			'title'     => ucfirst($tab),
			'props'     => [
				'tab' => $tab,
			],
		],
	];
}

// API: GET PAGINATED FILES
function getMediaFiles(string $tab, int $page, int $limit): array
{
	$validTabs = ['images', 'videos', 'documents', 'other'];
	if (!in_array($tab, $validTabs, true)) {
		return [
			'items' => [],
			'total' => 0,
			'page' => 1,
			'limit' => $limit,
			'pages' => 0,
		];
	}

	$cacheKey = 'media-files-index';
	$cache = getMediaCache();
	$index = $cache?->get($cacheKey);

	if ($index === null) {
		$index = buildMediaIndex();
		$cache?->set($cacheKey, $index, 60);
	}

	$fileIds = $index[$tab]['ids'] ?? [];
	$total = count($fileIds);

	$offset = ($page - 1) * $limit;
	$paginatedIds = array_slice($fileIds, $offset, $limit);

	$items = array_values(array_filter(array_map(function ($id) {
		$file = kirby()->file($id);
		return $file ? mapMediaFile($file) : null;
	}, $paginatedIds)));

	return [
		'items' => $items,
		'total' => $total,
		'page' => $page,
		'limit' => $limit,
		'pages' => (int) ceil($total / $limit),
	];
}

// BUILD INDEX OF FILE IDS
function buildMediaIndex(): array
{
	$kirby = kirby();
	$files = $kirby->site()
		->files()
		->add($kirby->site()->index()->files())
		->sortBy('modified', 'desc'); 

	$filters = [
		'images' => fn($f) => $f->type() === 'image',
		'videos' => fn($f) => $f->type() === 'video',
		'documents' => fn($f) => in_array($f->extension(), ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt'], true),
		'other'  => fn($f) => !in_array($f->type(), ['image', 'video'], true) &&
			!in_array($f->extension(), ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt'], true),
	];

	$index = [];
	foreach ($filters as $type => $filter) {
		$filtered = $files->filter($filter);
		$index[$type] = [
			'ids' => $filtered->pluck('id'),
			'count' => $filtered->count(),
		];
	}

	return $index;
}

// GET STATS
function getMediaStats(): array
{
	$cacheKey = 'media-files-index';
	$cache = getMediaCache();
	$index = $cache?->get($cacheKey);

	if ($index === null) {
		$index = buildMediaIndex();
		$cache?->set($cacheKey, $index, 60);
	}

	return [
		'images' => $index['images']['count'] ?? 0,
		'videos' => $index['videos']['count'] ?? 0,
		'documents' => $index['documents']['count'] ?? 0,
		'other' => $index['other']['count'] ?? 0,
	];
}

/* NORMALIZE DATA */
function mapMediaFile(File $file): array
{
	$type = $file->type();
	$dimensions = formatDimensions($file);
	$fileSize = formatSize($file->size());

	$info = array_filter([
		$dimensions,
		$fileSize
	]);

	return [
		'id'    => $file->id(),
		'text'  => $file->filename(),
		'image' => mediaThumb($file),
		'link'  => $file->panel()->url(true),
		'info'  => implode('<br>', $info)
	];
}

/* ITEM THUMBNAILS */
function mediaThumb(File $file): array
{
	$type = $file->type();

	$thumb = ['width' => 96, 'height' => 96, 'crop' => true, 'quality' => 80];
	$srcset = [
		'1x' => $thumb,
		'2x' => ['width' => 192, 'height' => 192, 'crop' => true, 'quality' => 80],
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

	// Return appropriate icon based on file extension
	$iconMap = [
		'pdf' => 'document',
		'doc' => 'document',
		'docx' => 'document',
		'txt' => 'text',
		'zip' => 'archive',
		'video' => 'video',
	];

	$icon = $iconMap[$file->extension()] ?? $iconMap[$type] ?? 'file';

	return ['icon' => $icon];
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
