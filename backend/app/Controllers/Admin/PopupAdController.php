<?php
namespace app\Controllers\Admin;

use app\Controllers\BaseController;
use PDO;

class PopupAdController extends BaseController
{
    public function index()
    {
        $stmt = $this->pdo->prepare('SELECT p.*, s.name as script_name FROM popup_ad p LEFT JOIN script s ON p.script_id = s.id ORDER BY p.sort_order ASC, p.id DESC');
        $stmt->execute();
        $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->success([
            'list' => $ads
        ]);
    }

    public function store()
    {
        $data = $this->jsonInput();
        $image = trim($data['image'] ?? '');
        $scriptId = (int) ($data['script_id'] ?? 0);
        $isActive = (int) ($data['is_active'] ?? 1);
        $sortOrder = (int) ($data['sort_order'] ?? 0);

        if (!$image || !$scriptId) {
            return $this->error('图片和剧本ID不能为空');
        }

        $stmt = $this->pdo->prepare('INSERT INTO popup_ad (image, script_id, is_active, sort_order) VALUES (?, ?, ?, ?)');
        $stmt->execute([$image, $scriptId, $isActive, $sortOrder]);

        return $this->success(['id' => $this->pdo->lastInsertId()]);
    }

    public function update($id)
    {
        $data = $this->jsonInput();
        $image = trim($data['image'] ?? '');
        $scriptId = (int) ($data['script_id'] ?? 0);
        $isActive = (int) ($data['is_active'] ?? 1);
        $sortOrder = (int) ($data['sort_order'] ?? 0);

        if (!$image || !$scriptId) {
            return $this->error('图片和剧本ID不能为空');
        }

        $stmt = $this->pdo->prepare('UPDATE popup_ad SET image = ?, script_id = ?, is_active = ?, sort_order = ? WHERE id = ?');
        $stmt->execute([$image, $scriptId, $isActive, $sortOrder, $id]);

        return $this->success();
    }

    public function destroy($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM popup_ad WHERE id = ?');
        $stmt->execute([$id]);

        return $this->success();
    }

    public function getActiveAd()
    {
        $stmt = $this->pdo->prepare('SELECT p.*, s.name as script_name FROM popup_ad p LEFT JOIN script s ON p.script_id = s.id WHERE p.is_active = 1 ORDER BY p.sort_order ASC, p.id DESC LIMIT 1');
        $stmt->execute();
        $ad = $stmt->fetch(PDO::FETCH_ASSOC);

        return $this->success(['ad' => $ad]);
    }
}