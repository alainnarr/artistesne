<?php

declare(strict_types=1);

namespace App\Enums;

enum LinkType: string
{
    case COLLABORATION = 'collaboration';
    case WEBSITE = 'website';
    case FACEBOOK = 'facebook';
    case INSTAGRAM = 'instagram';
    case TIKTOK = 'tiktok';
    case YOUTUBE = 'youtube';
    case VIMEO = 'vimeo';
    case BANDCAMP = 'bandcamp';
    case SOUNDCLOUD = 'soundcloud';
    case SPOTIFY = 'spotify';
    case LINKEDIN = 'linkedin';
    case X = 'x';
    case DEVIANTART = 'deviantart';
    case TWITCH = 'twitch';
    case PINTEREST = 'pinterest';
    case FLICKR = 'flickr';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::COLLABORATION => 'Collaboration',
            self::WEBSITE => 'Site personnel',
            self::FACEBOOK => 'Facebook',
            self::INSTAGRAM => 'Instagram',
            self::TIKTOK => 'TikTok',
            self::YOUTUBE => 'YouTube',
            self::VIMEO => 'Vimeo',
            self::BANDCAMP => 'Bandcamp',
            self::SOUNDCLOUD => 'SoundCloud',
            self::SPOTIFY => 'Spotify',
            self::LINKEDIN => 'LinkedIn',
            self::X => 'X (Twitter)',
            self::TWITCH => 'Twitch',
            self::PINTEREST => 'Pinterest',
            self::FLICKR => 'Flickr',
            self::DEVIANTART => 'DeviantArt',
            self::OTHER => 'Autre',
        };
    }
}
