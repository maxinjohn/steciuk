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
    case SermonList = 'sermon_list';
    case Gallery = 'gallery';
    case Faq = 'faq';
    case Contact = 'contact';
    case Location = 'location';
    case Quote = 'quote';
    case Downloads = 'downloads';
    case Youtube = 'youtube';
    case Map = 'map';
}
