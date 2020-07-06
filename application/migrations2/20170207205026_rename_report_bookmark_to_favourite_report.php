<?php
class Migration_20170207205026_RenameReportBookmarkToFavouriteReport extends Migration 
{
    const DESCRIPTION_RU = 'Переименовка report_bookmarks в favourite_reports';

    const DESCRIPTION_EN = 'Rename report_bookmarks to favourite_reports';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "RENAME TABLE {$prefix}report_bookmarks TO {$prefix}favourite_reports";
        self::execute($sql);
    }
}