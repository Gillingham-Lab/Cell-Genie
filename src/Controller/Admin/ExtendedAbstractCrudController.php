<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/**
 * @template TEntity of AbstractCrudController
 * @extends AbstractCrudController<TEntity>
 */
abstract class ExtendedAbstractCrudController extends AbstractCrudController
{


}