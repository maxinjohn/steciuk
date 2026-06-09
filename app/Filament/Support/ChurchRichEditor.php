<?php

namespace App\Filament\Support;

use Filament\Forms\Components\RichEditor;

class ChurchRichEditor
{
    /**
     * Rich editor configured for parish prose — matches frontend purifier rules.
     *
     * @param  list<string>  $extraButtons
     */
    public static function make(string $name, array $extraButtons = []): RichEditor
    {
        return RichEditor::make($name)
            ->toolbarButtons(array_values(array_unique(array_merge([
                'bold',
                'italic',
                'underline',
                'strike',
                'link',
                'h2',
                'h3',
                'blockquote',
                'bulletList',
                'orderedList',
                'table',
                'attachFiles',
                'undo',
                'redo',
            ], $extraButtons))))
            ->fileAttachmentsDirectory('rich-content')
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsMaxSize(5120)
            ->fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->columnSpanFull();
    }
}
