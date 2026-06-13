<?php

namespace App\Enums;

enum ContentBlockType: string
{
    case Hero = 'hero';
    case TextImage = 'text_image';
    case ImageText = 'image_text';
    case Cta = 'cta';
    case MinistryCards = 'ministry_cards';
    case EventList = 'event_list';
    case NewsList = 'news_list';
    case SermonList = 'sermon_list';
    case Gallery = 'gallery';
    case Faq = 'faq';
    case Contact = 'contact';
    case Location = 'location';
    case Quote = 'quote';
    case Downloads = 'downloads';
    case Youtube = 'youtube';
    case Map = 'map';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'Hero banner',
            self::TextImage => 'Text + image',
            self::ImageText => 'Image + text',
            self::Cta => 'Call to action',
            self::MinistryCards => 'Ministry cards',
            self::EventList => 'Event list',
            self::NewsList => 'News list',
            self::SermonList => 'Sermon list',
            self::Gallery => 'Gallery preview',
            self::Faq => 'FAQ accordion',
            self::Contact => 'Contact form',
            self::Location => 'Location tabs',
            self::Quote => 'Quote block',
            self::Downloads => 'Downloads link',
            self::Youtube => 'YouTube video',
            self::Map => 'Map embed',
        };
    }
}
