<?php
if ( ! defined('EVENT_ESPRESSO_VERSION')) {
    exit('NO direct script access allowed');
}


/*                  Experiments_Admin_Page_Init
 *                  This is the init for the EE Support Admin Pages.  See EE_Admin_Page_Init for method inline docs.
 * @package         Experiments_Admin_Page_Init
 * @subpackage      includes/core/admin/Experiments_Admin_Page_Init.core.php
 * @author          Darren Ethier
 *                  ------------------------------------------------------------------------
 */
class Experiments_Admin_Page_Init extends EE_Admin_Page_Init
{


    public function __construct()
    {
        //define some help/support page related constants
        define('EE_EXPERIMENTS_SLUG', 'espresso_experiments');
        define('EE_EXPERIMENTS_ADMIN_URL', admin_url('admin.php?page=' . EE_EXPERIMENTS_SLUG));
        define('EE_EXPERIMENTS_ADMIN_TEMPLATE_PATH', EE_ADMIN_PAGES . 'experiments/templates/');
        define('EE_EXPERIMENTS_ADMIN', EE_ADMIN_PAGES . 'experiments/');
        define('EE_EXPERIMENTS_ASSETS_URL', EE_ADMIN_PAGES_URL . 'experiments/assets/');
        parent::__construct();
    }

    protected function _set_init_properties()
    {
        $this->label = __('Experiments', 'event_espresso');
    }

    protected function _set_menu_map()
    {
        $this->_menu_map = new EE_Admin_Page_Sub_Menu(array(
            'menu_group'              => 'extras',
            'menu_order'              => 50,
            'show_on_menu'            => EE_Admin_Page_Menu_Map::BLOG_AND_NETWORK_ADMIN,
            'parent_slug'             => 'espresso_events',
            'menu_slug'               => EE_EXPERIMENTS_SLUG,
            'menu_label'              => __('EE Experiments', 'event_espresso'),
            'capability'              => 'ee_read_ee',
            'maintenance_mode_parent' => 'espresso_maintenance_settings',
            'admin_init_page'         => $this,
        ));
    }

} //end class Experiments_Admin_Page_Init
