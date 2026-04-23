# 鲸落密室

密室逃脱剧本展示系统，包含后端 API、管理员后台、品牌方后台、小程序前端，以及一套独立的小程序实验前端代码。

## 项目结构

- `backend/`：PHP 后端接口与上传资源目录
- `admin/`：管理员/品牌方后台，Vue 3 + Vite + Element Plus
- `miniprogram/`：当前实际使用的小程序工程与编译产物
- `a/`：另一套独立前端实现，用于页面与交互实验
- `shared/`：共享数据资源

## 当前技术栈

### 后端
- PHP 8.0
- 原生 PHP + PDO
- MySQL 8.0

### 后台
- Vue 3
- Vite
- Element Plus
- Pinia

### 小程序
- Uni-App / Vue 3
- 微信小程序编译产物位于 `miniprogram/dist/dev/mp-weixin`

## 本地环境要求

- PHP 8.0+
- MySQL 8.0+
- Node.js 16+
- npm

## 数据库默认配置

后端默认使用：

- 数据库名：`think`
- 用户名：`think`
- 密码：`123456`

## 初始化数据库

可导入：

```bash
mysql -u think -p123456 think < backend/database/migrations/002_simple_schema.sql
```

后续新增结构通过迁移文件补齐：

- `003_add_script_video_and_content.sql`
- `004_normalize_script_type_labels.sql`
- `005_normalize_category_labels.sql`
- `006_expand_categories_to_match_types.sql`
- `007_add_script_collect_and_purchase_counts.sql`
- `008_add_script_home_featured_flag.sql`
- `009_add_script_home_featured_sort.sql`
- `010_add_script_purchase_intent_table.sql`

## 本地启动

### 1. 启动后端

在 `backend/public` 目录运行：

```bash
php -S 0.0.0.0:8090 router.php
```

说明：
- 开发者工具访问通常可走 `127.0.0.1:8090`
- 真机调试需确保局域网地址可访问，例如 `192.168.x.x:8090`

### 2. 启动后台管理端

```bash
cd admin
npm install
npm run dev
```

### 3. 编译小程序

```bash
cd miniprogram
npm install
npm run build:mp-weixin
```

当前小程序实际调试目录通常使用：

```text
miniprogram/dist/dev/mp-weixin
```

## 管理后台能力

### 管理员后台
- 分类管理
- 品牌管理
- 剧本管理
- 首页轮播剧本置顶与排序
- 首页内容管理
- 剧本购买意向查看
- 施工权限/施工案例管理
- 社区内容管理

### 品牌方后台
- 我的剧本管理
- 剧本详情预览
- 资料完整度提示
- 视频/图集上传

## 当前已实现的重要能力

### 剧本管理
- 自定义编辑浏览数、点赞数、收藏数、已购买数
- 设置首页轮播置顶
- 设置首页轮播排序值
- 编辑页内前台详情预览
- 资料缺失项提示与定位

### 小程序前端
- 首页轮播支持剧本封面置顶展示
- 点击轮播图直达剧本详情
- 剧本详情支持图片、视频、详细介绍
- 价格、浏览、点赞、收藏、已购数据展示

### 剧本购买意向
- 前端可提交购买意向
- 后台管理员可查看意向客户信息
- 品牌方不可查看买家联系方式

## 默认后台账号

### 管理员
- 用户名：`admin`
- 密码：`admin123`

## 调试说明

### 小程序开发者工具有数据但真机无数据
通常是以下原因：

1. 后端只监听了 `127.0.0.1`
2. 局域网 IP 不可访问
3. Windows 防火墙未放行 8090
4. 小程序仍使用旧缓存

建议：

- 后端用 `0.0.0.0:8090` 启动
- 开发者工具清除数据缓存后重新编译
- 真机与电脑处于同一局域网

## 上传文件说明

上传文件保存在：

```text
backend/public/uploads/
```

当前支持：
- 图片：jpg / jpeg / png / gif / webp
- 视频：mp4 / mov / webm

## 安全说明

以下文件不应提交到仓库：

- `账号密码.txt`
- 任何包含真实账号、密钥、服务器口令的文件

本仓库已通过 `.gitignore` 排除该敏感文件。

## 仓库地址

GitHub：

`https://github.com/19871017/jingluo-mishi`
