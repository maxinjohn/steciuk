<?php

namespace App\Enums;

enum AdminPermission: string
{
    case AdminAccess = 'admin.access';

    case PagesViewAny = 'pages.viewAny';
    case PagesView = 'pages.view';
    case PagesCreate = 'pages.create';
    case PagesUpdate = 'pages.update';
    case PagesDelete = 'pages.delete';
    case PagesRestore = 'pages.restore';
    case PagesForceDelete = 'pages.forceDelete';

    case EventsViewAny = 'events.viewAny';
    case EventsView = 'events.view';
    case EventsCreate = 'events.create';
    case EventsUpdate = 'events.update';
    case EventsDelete = 'events.delete';
    case EventsRestore = 'events.restore';
    case EventsForceDelete = 'events.forceDelete';

    case NewsViewAny = 'news.viewAny';
    case NewsView = 'news.view';
    case NewsCreate = 'news.create';
    case NewsUpdate = 'news.update';
    case NewsDelete = 'news.delete';
    case NewsRestore = 'news.restore';
    case NewsForceDelete = 'news.forceDelete';

    case SermonsViewAny = 'sermons.viewAny';
    case SermonsView = 'sermons.view';
    case SermonsCreate = 'sermons.create';
    case SermonsUpdate = 'sermons.update';
    case SermonsDelete = 'sermons.delete';
    case SermonsRestore = 'sermons.restore';
    case SermonsForceDelete = 'sermons.forceDelete';

    case MinistriesViewAny = 'ministries.viewAny';
    case MinistriesView = 'ministries.view';
    case MinistriesCreate = 'ministries.create';
    case MinistriesUpdate = 'ministries.update';
    case MinistriesDelete = 'ministries.delete';
    case MinistriesRestore = 'ministries.restore';
    case MinistriesForceDelete = 'ministries.forceDelete';

    case MenuItemsViewAny = 'menu_items.viewAny';
    case MenuItemsView = 'menu_items.view';
    case MenuItemsCreate = 'menu_items.create';
    case MenuItemsUpdate = 'menu_items.update';
    case MenuItemsDelete = 'menu_items.delete';
    case MenuItemsRestore = 'menu_items.restore';
    case MenuItemsForceDelete = 'menu_items.forceDelete';

    case ContentBlocksViewAny = 'content_blocks.viewAny';
    case ContentBlocksView = 'content_blocks.view';
    case ContentBlocksCreate = 'content_blocks.create';
    case ContentBlocksUpdate = 'content_blocks.update';
    case ContentBlocksDelete = 'content_blocks.delete';
    case ContentBlocksRestore = 'content_blocks.restore';
    case ContentBlocksForceDelete = 'content_blocks.forceDelete';

    case GalleryAlbumsViewAny = 'gallery_albums.viewAny';
    case GalleryAlbumsView = 'gallery_albums.view';
    case GalleryAlbumsCreate = 'gallery_albums.create';
    case GalleryAlbumsUpdate = 'gallery_albums.update';
    case GalleryAlbumsDelete = 'gallery_albums.delete';
    case GalleryAlbumsRestore = 'gallery_albums.restore';
    case GalleryAlbumsForceDelete = 'gallery_albums.forceDelete';

    case GalleryPhotosViewAny = 'gallery_photos.viewAny';
    case GalleryPhotosView = 'gallery_photos.view';
    case GalleryPhotosCreate = 'gallery_photos.create';
    case GalleryPhotosUpdate = 'gallery_photos.update';
    case GalleryPhotosDelete = 'gallery_photos.delete';
    case GalleryPhotosRestore = 'gallery_photos.restore';
    case GalleryPhotosForceDelete = 'gallery_photos.forceDelete';

    case ParishResourcesViewAny = 'parish_resources.viewAny';
    case ParishResourcesView = 'parish_resources.view';
    case ParishResourcesCreate = 'parish_resources.create';
    case ParishResourcesUpdate = 'parish_resources.update';
    case ParishResourcesDelete = 'parish_resources.delete';
    case ParishResourcesRestore = 'parish_resources.restore';
    case ParishResourcesForceDelete = 'parish_resources.forceDelete';

    case ServicesViewAny = 'services.viewAny';
    case ServicesView = 'services.view';
    case ServicesCreate = 'services.create';
    case ServicesUpdate = 'services.update';
    case ServicesDelete = 'services.delete';
    case ServicesRestore = 'services.restore';
    case ServicesForceDelete = 'services.forceDelete';

    case FormSubmissionsViewAny = 'form_submissions.viewAny';
    case FormSubmissionsView = 'form_submissions.view';
    case FormSubmissionsCreate = 'form_submissions.create';
    case FormSubmissionsUpdate = 'form_submissions.update';
    case FormSubmissionsDelete = 'form_submissions.delete';
    case FormSubmissionsRestore = 'form_submissions.restore';
    case FormSubmissionsForceDelete = 'form_submissions.forceDelete';

    case UsersViewAny = 'users.viewAny';
    case UsersView = 'users.view';
    case UsersCreate = 'users.create';
    case UsersUpdate = 'users.update';
    case UsersDelete = 'users.delete';
    case UsersRestore = 'users.restore';
    case UsersForceDelete = 'users.forceDelete';

    case SettingsChurch = 'settings.church';
    case SettingsMail = 'settings.mail';
    case SettingsPermissions = 'settings.permissions';
    case SecurityAuditLog = 'security.audit_log';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $permission) => [$permission->value => $permission->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::AdminAccess => 'Access admin panel',
            self::PagesViewAny => 'Pages — list',
            self::PagesView => 'Pages — view',
            self::PagesCreate => 'Pages — create',
            self::PagesUpdate => 'Pages — edit',
            self::PagesDelete => 'Pages — delete',
            self::PagesRestore => 'Pages — restore',
            self::PagesForceDelete => 'Pages — permanently delete',
            self::EventsViewAny => 'Events — list',
            self::EventsView => 'Events — view',
            self::EventsCreate => 'Events — create',
            self::EventsUpdate => 'Events — edit',
            self::EventsDelete => 'Events — delete',
            self::EventsRestore => 'Events — restore',
            self::EventsForceDelete => 'Events — permanently delete',
            self::NewsViewAny => 'News — list',
            self::NewsView => 'News — view',
            self::NewsCreate => 'News — create',
            self::NewsUpdate => 'News — edit',
            self::NewsDelete => 'News — delete',
            self::NewsRestore => 'News — restore',
            self::NewsForceDelete => 'News — permanently delete',
            self::SermonsViewAny => 'Sermons — list',
            self::SermonsView => 'Sermons — view',
            self::SermonsCreate => 'Sermons — create',
            self::SermonsUpdate => 'Sermons — edit',
            self::SermonsDelete => 'Sermons — delete',
            self::SermonsRestore => 'Sermons — restore',
            self::SermonsForceDelete => 'Sermons — permanently delete',
            self::MinistriesViewAny => 'Ministries — list',
            self::MinistriesView => 'Ministries — view',
            self::MinistriesCreate => 'Ministries — create',
            self::MinistriesUpdate => 'Ministries — edit',
            self::MinistriesDelete => 'Ministries — delete',
            self::MinistriesRestore => 'Ministries — restore',
            self::MinistriesForceDelete => 'Ministries — permanently delete',
            self::MenuItemsViewAny => 'Menu items — list',
            self::MenuItemsView => 'Menu items — view',
            self::MenuItemsCreate => 'Menu items — create',
            self::MenuItemsUpdate => 'Menu items — edit',
            self::MenuItemsDelete => 'Menu items — delete',
            self::MenuItemsRestore => 'Menu items — restore',
            self::MenuItemsForceDelete => 'Menu items — permanently delete',
            self::ContentBlocksViewAny => 'Content blocks — list',
            self::ContentBlocksView => 'Content blocks — view',
            self::ContentBlocksCreate => 'Content blocks — create',
            self::ContentBlocksUpdate => 'Content blocks — edit',
            self::ContentBlocksDelete => 'Content blocks — delete',
            self::ContentBlocksRestore => 'Content blocks — restore',
            self::ContentBlocksForceDelete => 'Content blocks — permanently delete',
            self::GalleryAlbumsViewAny => 'Gallery albums — list',
            self::GalleryAlbumsView => 'Gallery albums — view',
            self::GalleryAlbumsCreate => 'Gallery albums — create',
            self::GalleryAlbumsUpdate => 'Gallery albums — edit',
            self::GalleryAlbumsDelete => 'Gallery albums — delete',
            self::GalleryAlbumsRestore => 'Gallery albums — restore',
            self::GalleryAlbumsForceDelete => 'Gallery albums — permanently delete',
            self::GalleryPhotosViewAny => 'Gallery photos — list',
            self::GalleryPhotosView => 'Gallery photos — view',
            self::GalleryPhotosCreate => 'Gallery photos — create',
            self::GalleryPhotosUpdate => 'Gallery photos — edit',
            self::GalleryPhotosDelete => 'Gallery photos — delete',
            self::GalleryPhotosRestore => 'Gallery photos — restore',
            self::GalleryPhotosForceDelete => 'Gallery photos — permanently delete',
            self::ParishResourcesViewAny => 'Parish resources — list',
            self::ParishResourcesView => 'Parish resources — view',
            self::ParishResourcesCreate => 'Parish resources — create',
            self::ParishResourcesUpdate => 'Parish resources — edit',
            self::ParishResourcesDelete => 'Parish resources — delete',
            self::ParishResourcesRestore => 'Parish resources — restore',
            self::ParishResourcesForceDelete => 'Parish resources — permanently delete',
            self::ServicesViewAny => 'Services — list',
            self::ServicesView => 'Services — view',
            self::ServicesCreate => 'Services — create',
            self::ServicesUpdate => 'Services — edit',
            self::ServicesDelete => 'Services — delete',
            self::ServicesRestore => 'Services — restore',
            self::ServicesForceDelete => 'Services — permanently delete',
            self::FormSubmissionsViewAny => 'Form submissions — list',
            self::FormSubmissionsView => 'Form submissions — view',
            self::FormSubmissionsCreate => 'Form submissions — create',
            self::FormSubmissionsUpdate => 'Form submissions — edit',
            self::FormSubmissionsDelete => 'Form submissions — delete',
            self::FormSubmissionsRestore => 'Form submissions — restore',
            self::FormSubmissionsForceDelete => 'Form submissions — permanently delete',
            self::UsersViewAny => 'Users — list',
            self::UsersView => 'Users — view',
            self::UsersCreate => 'Users — create',
            self::UsersUpdate => 'Users — edit',
            self::UsersDelete => 'Users — delete',
            self::UsersRestore => 'Users — restore',
            self::UsersForceDelete => 'Users — permanently delete',
            self::SettingsChurch => 'Church & faith settings',
            self::SettingsMail => 'SMTP & email settings',
            self::SettingsPermissions => 'Role permissions',
            self::SecurityAuditLog => 'Security audit log',
        };
    }

    /**
     * @return array<string, list<self>>
     */
    public static function grouped(): array
    {
        return [
            'Administration' => [self::AdminAccess, self::UsersViewAny, self::UsersView, self::UsersCreate, self::UsersUpdate, self::UsersDelete, self::UsersRestore, self::UsersForceDelete, self::SettingsChurch, self::SettingsMail, self::SettingsPermissions, self::SecurityAuditLog],
            'Pages & blocks' => array_values(array_filter(self::cases(), fn (self $p) => str_starts_with($p->value, 'pages.') || str_starts_with($p->value, 'content_blocks.') || str_starts_with($p->value, 'menu_items.'))),
            'Worship & media' => array_values(array_filter(self::cases(), fn (self $p) => str_starts_with($p->value, 'events.') || str_starts_with($p->value, 'news.') || str_starts_with($p->value, 'sermons.') || str_starts_with($p->value, 'ministries.') || str_starts_with($p->value, 'services.'))),
            'Gallery & resources' => array_values(array_filter(self::cases(), fn (self $p) => str_starts_with($p->value, 'gallery_') || str_starts_with($p->value, 'parish_resources.'))),
            'Forms' => array_values(array_filter(self::cases(), fn (self $p) => str_starts_with($p->value, 'form_submissions.'))),
        ];
    }

    public static function forResourceAction(string $resource, string $action): ?self
    {
        $key = $resource.'.'.$action;

        return self::tryFrom($key);
    }
}
