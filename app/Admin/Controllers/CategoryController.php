<?php

namespace App\Admin\Controllers;

use OpenAdmin\Admin\Controllers\AdminController;
use OpenAdmin\Admin\Form;
use OpenAdmin\Admin\Grid;
use OpenAdmin\Admin\Show;
use \App\Models\Category;

class CategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Category';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Category());

        $grid->quickSearch('name');
        $grid->model()->orderBy('id', 'desc');
        $grid->id('ID')->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('image', __('Image'))->image('', 100, 100);
        $grid->column('type', __('Type'))->sortable();
        $grid->column('inactive', __('Inactive'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Category::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('image', __('Image'));
        $show->field('type', __('Type'));
        $show->field('inactive', __('Inactive'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Category());

        $form->number('id', 'ID');
        $form->text('name', __('Name'));
        $form->url('image', __('Image'));
        $form->select('type', __('Type'))
            ->options([0 => 'Recipe', 1 => 'Snack', 2 => 'Kitchen Help'])
            ->default(0);
        $form->switch('inactive', __('Inactive'));
        $form->datetime('created_at', __('Created at'))->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
