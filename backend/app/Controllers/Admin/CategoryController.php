<?php
namespace app\controller\Admin;

use app\model\Category;
use app\controller\BaseController;

class CategoryController extends BaseController
{
    public function list()
    {
        $categories = Category::order('sort_order', 'asc')->select();

        return $this->success([
            'list' => $categories,
        ]);
    }

    public function create()
    {
        $name = $this->request->param('name');
        $sortOrder = (int) $this->request->param('sort_order', 0);

        if (empty($name)) {
            return $this->error('Name is required');
        }

        $category = Category::create([
            'name' => $name,
            'sort_order' => $sortOrder,
        ]);

        return $this->success($category, 'Created successfully', 201);
    }

    public function update(int $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->error('Category not found', 404);
        }

        $data = $this->request->only(['name', 'sort_order']);
        $category->save($data);

        return $this->success(null, 'Updated successfully');
    }

    public function delete(int $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->error('Category not found', 404);
        }

        $category->delete();

        return $this->success(null, 'Deleted successfully');
    }
}
