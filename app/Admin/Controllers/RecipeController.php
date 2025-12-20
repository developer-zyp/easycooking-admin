<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Str;
use OpenAdmin\Admin\Controllers\AdminController;
use OpenAdmin\Admin\Form;
use OpenAdmin\Admin\Grid;
use OpenAdmin\Admin\Show;
use \App\Models\Recipe;

class RecipeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Recipe';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Recipe());

        $grid->quickSearch('name');
        $grid->filter(function ($filter) {
            $filter->equal('category_id', 'Category')->select(Category::all()->pluck('name', 'id')->prepend('Default Category', 0));
            $filter->equal('post_id', 'Post')->select(Post::all()->pluck('title', 'id')->prepend('Default Post', 0));
        });
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('Id'))->sortable();
        $grid->column('slug', __('Slug'))->sortable();
        $grid->column('name', __('Name'));
        $grid->column('image', __('Image'))->image('', 100, 100);
        $grid->column('category_id', __('Category id'))->sortable();
        $grid->column('category.name')->hide();
        $grid->column('post_id', __('Post id'))->sortable();
        $grid->column('post.title')->hide();
        // $grid->column('description', __('Description'));
        // $grid->column('instructions', __('Instructions'));
        // $grid->column('prep_time', __('Prep time'));
        // $grid->column('cook_time', __('Cook time'));
        $grid->column('status', __('Status'))->sortable();
        $grid->column('view_count', __('View count'));
        $grid->column('fav_count', __('Fav count'));
        $grid->column('inactive', __('Inactive'));
        $grid->column('created_at', __('Created at'))->dateFormat('Y-m-d H:i:s')->sortable();
        $grid->column('updated_at', __('Updated at'))->dateFormat('Y-m-d H:i:s')->sortable();

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
        $show = new Show(Recipe::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('slug', __('Slug'));
        $show->field('category_id', __('Category id'));
        $show->field('post_id', __('Post id'));
        $show->field('name', __('Name'));
        $show->field('image', __('Image'))->image();
        $show->field('description', __('Description'));
        $show->field('instructions', __('Instructions'));
        $show->field('prep_time', __('Prep time'));
        $show->field('cook_time', __('Cook time'));
        $show->field('status', __('Status'));
        $show->field('view_count', __('View count'));
        $show->field('fav_count', __('Fav count'));
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
        $form = new Form(new Recipe());

        $form->number('id', 'ID');
        $form->text('slug', __('Slug'))
            ->default(function ($form) {
                return 'recipe-' . Str::random(10);
            });
//        $form->display('category_id', __('Category Id'));
        $form->select('category_id', __('Category'))
            ->options(Category::all()->pluck('name', 'id')->prepend('Default Category', 0))
            ->default(0);
//        $form->display('post_id', __('Post Id'));
        $form->select('post_id', __('Post'))
            ->options(Post::all()->pluck('title', 'id')->prepend('Default Post', 0))
            ->default(0);
        $form->text('name', __('Name'));
        $form->url('image', __('Image'));
        $form->ckeditor('description', __('Description'));
        $form->textarea('instructions', __('Instructions'));
        $form->number('prep_time', __('Prep time'));
        $form->number('cook_time', __('Cook time'));
//        $form->text('status', __('Status'))->default('draft'); //'draft', 'published', 'archived'
        $form->select('status', 'Status')
            ->options(['published' => 'published', 'draft' => 'draft', 'archived' => 'archived'])
            ->default('published');
        $form->number('view_count', __('View count'))->default(1);
        $form->number('fav_count', __('Fav count'))->default(1);
        $form->switch('inactive', __('Inactive'));
        $form->datetime('created_at', __('Created at'))->default(date('Y-m-d H:i:s'));

        $form->hasMany('images', 'Recipe Images', function (Form\NestedForm $imageForm) {
            $imageForm->text('name', __('Name'))
                ->default(Str::random(10));
            $imageForm->url('url', __('Url'));
            $imageForm->select('content_type', __('Content type'))
                ->options(['image' => 'Image', 'youtube' => 'Youtube'])
                ->default('image');
            $imageForm->text('video_id', __('Video id'));
            $imageForm->url('video_url', __('Video url'));
        });

        return $form;
    }
}
