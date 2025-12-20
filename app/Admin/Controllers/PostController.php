<?php

namespace App\Admin\Controllers;

use Illuminate\Support\Str;
use OpenAdmin\Admin\Controllers\AdminController;
use OpenAdmin\Admin\Form;
use OpenAdmin\Admin\Grid;
use OpenAdmin\Admin\Show;
use \App\Models\Post;

class PostController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Post';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Post());

        $grid->quickSearch('title');
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('Id'))->sortable();
        $grid->column('slug', __('Slug'))->sortable();
        $grid->column('title', __('Title'));
        $grid->column('tags', __('Tags'));
        $grid->column('status', __('Status'))->sortable();
        // $grid->column('content', __('Content'));
        $grid->column('view_count', __('View count'))->sortable();
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
        $show = new Show(Post::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('slug', __('Slug'));
        $show->field('tags', __('Tags'));
        $show->field('status', __('Status'));
        $show->field('content', __('Content'));
        $show->field('view_count', __('View count'));
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
        $form = new Form(new Post());

        $form->number('id', 'ID');
        $form->text('slug', __('Slug'))
            ->default(function ($form) {
                return 'post-' . Str::random(10);
            });
        $form->text('title', __('Title'));
        $form->text('tags', __('Tags'));
        $form->ckeditor('content', __('Content'));
        $form->select('status', __('Status'))
            ->options(['published' => 'published', 'draft' => 'draft', 'archived' => 'archived'])
            ->default('published');
        $form->number('view_count', __('View count'))->default(1);
        $form->datetime('created_at', __('Created at'))->default(date('Y-m-d H:i:s'));

        $form->hasMany('images', 'Post Images', function (Form\NestedForm $imageForm) {
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
