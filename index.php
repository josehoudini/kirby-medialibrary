<?php

use Kirby\Cms\App as Kirby;

Kirby::plugin('josehoudini/media-library', [
	'areas' => [
		'media-library' => fn ($kirby) => [
		'label' => 'Files',
		'icon'  => 'folder',
		'menu'  => true,
		'link'  => 'media-library',
		'views' => [
			[
			'pattern' => 'media-library',
			'action' => fn () => [
				'component' => 'sitefiles',
				'title'     => 'Files',
				'props' => [
					'images' => $kirby->site()->index()->files()->add($kirby->site()->files())
					->filterBy('type', 'image')
					->map(fn($file) => [
						'id'    => $file->id(),
						'text'  => $file->name() . '.' . $file->extension(),
						'image' => [
						'src'    => $file->thumb(['width' => 96, 'height' => 96, 'crop' => true])->url() ?: $file->url(),
						'srcset' => $file->srcset([
							'1x' => ['width' => 96, 'height' => 96, 'crop' => true],
							'2x' => ['width' => 192, 'height' => 192, 'crop' => true],
						])
						],
						'link'  => $file->panel()->url(true),
						'info'  => number_format($file->size() / 1024 / 1024, 2) . ' MB'
					])->values()
				],
			]
			]
		]
		]
	]
]);
