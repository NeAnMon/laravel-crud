<?php

namespace Bgaze\Crud\Themes\Classic;

use Bgaze\Crud\Themes\Api\Crud as Base;
use Bgaze\Crud\Themes\Classic\Builders;

/**
 * The core class of the CRUD theme
 *
 * @author bgaze <benjamin@bgaze.fr>
 */
class Crud extends Base {

    /**
     * The unique name of the CRUD theme.
     * 
     * @return string
     */
    static public function name() {
        return 'default:classic';
    }

    /**
     * The stubs availables in the CRUD theme.
     * 
     * @return array Name as key, absolute path as value.
     */
    static public function stubs() {
        return array_merge(parent::stubs(), [
            'partials.index-head' => __DIR__ . '/Stubs/partials/index-head.stub',
            'partials.index-body' => __DIR__ . '/Stubs/partials/index-body.stub',
            'partials.show-group' => __DIR__ . '/Stubs/partials/show-group.stub',
            'partials.form-group' => __DIR__ . '/Stubs/partials/form-group.stub',
            'views.index' => __DIR__ . '/Stubs/views/index.stub',
            'views.show' => __DIR__ . '/Stubs/views/show.stub',
            'views.create' => __DIR__ . '/Stubs/views/create.stub',
            'views.edit' => __DIR__ . '/Stubs/views/edit.stub',
            'controller' => __DIR__ . '/Stubs/controller.stub',
            'routes' => __DIR__ . '/Stubs/routes.stub',
        ]);
    }

    /**
     * The builders availables in the CRUD theme.
     * 
     * @return array Name as key, full class name as value.
     */
    static public function builders() {
        return array_merge(parent::builders(), [
            'controller-class' => Builders\Controller::class,
            'index-view' => Builders\Views\Index::class,
            'create-view' => Builders\Views\Create::class,
            'edit-view' => Builders\Views\Edit::class,
            'show-view' => Builders\Views\Show::class,
        ]);
    }

    /**
     * Get CRUD index url to display in successfull generation message.
     * 
     * Cannot be generated with route() helper as created routes are not
     * loaded in current process.
     * 
     * @return string
     */
    public function indexPath() {
        return url($this->getPluralsKebabSlash());
    }

}
