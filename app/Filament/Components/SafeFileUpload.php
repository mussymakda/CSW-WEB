<?php

namespace App\Filament\Components;

use Filament\Forms\Components\FileUpload as BaseFileUpload;

class SafeFileUpload extends BaseFileUpload
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->rules([
            'image',
            'max:5120', // 5MB max
            'mimes:jpeg,png,gif,webp',
        ])
            ->maxSize(5120)
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->removeUploadedFileButtonPosition('right')
            ->uploadButtonPosition('left')
            ->previewable(false)
            ->validationMessages([
                'image' => 'The file must be a valid image.',
                'max' => 'The image may not be larger than 5MB.',
                'mimes' => 'The image must be a JPEG, PNG, GIF, or WebP file.',
            ]);
    }

    public static function make(string $name): static
    {
        $static = parent::make($name);

        // Add automatic resize settings to prevent processing issues
        $static->imageResizeMode('cover');

        return $static;
    }
}
