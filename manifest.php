<?php class manifest
{

    # the directory that olive.php is in it based on server root directory
    # use a slash in case you put the project inside of server root directory (www, public_html,...)
    public const root_path = 'OlivePHP';

    # view directory
    public const view_dir = 'App/Views';

    #
    public const default_timezone = 'GMT';

    # default encoding for mb_*
    public const default_encoding = 'UTF-8';

    # defines DEBUG_MODE constant
    public const debug = true;

}