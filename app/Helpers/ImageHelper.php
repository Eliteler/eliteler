<?php

namespace App\Helpers;

/**
 * @deprecated Renamed to StorageHelper. Kept so plugins and templates built
 *             against older versions (which call ImageHelper::uploadImage /
 *             ImageHelper::deleteFile) keep working. Use StorageHelper.
 */
class ImageHelper extends StorageHelper
{
}
