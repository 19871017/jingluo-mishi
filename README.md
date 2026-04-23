# 密室逃脱剧本展示系统

## 项目概述

密室逃脱剧本展示系统是一个专为密室逃脱行业设计的综合管理平台，包含后端API、后台管理系统和小程序前端三个部分。该系统旨在帮助品牌方、施工方和用户之间建立更好的沟通和展示渠道。

## 技术栈

### 后端
- **语言**：PHP 8.0.2
- **框架**：原生PHP + PDO
- **数据库**：MySQL 8.0.12
- **依赖管理**：Composer 2.5.8

### 前端
- **后台管理系统**：Vue 3 + Vite + Element Plus + Pinia
- **小程序前端**：Vue 3 + Vite + Uni-App

## 系统架构

- **backend/**：后端API服务
- **admin/**：后台管理系统
- **miniprogram/**：小程序前端

## 本地环境搭建

### 1. 环境准备

- PHP 8.0.2（推荐使用PHPStudy）
- MySQL 8.0.12
- Composer 2.5.8
- Node.js 16+

### 2. 数据库配置

1. 创建数据库：`think`
2. 用户名：`think`
3. 密码：`123456`
4. 导入数据库结构：
   ```bash
   mysql -u think -p123456 think < backend/database/migrations/002_simple_schema.sql
   ```

### 3. 依赖安装

#### 后端
```bash
cd backend
composer install
```

#### 后台管理系统
```bash
cd admin
npm install
```

#### 小程序前端
```bash
cd miniprogram
npm install
```

## 运行项目

### 1. 启动后端服务

```bash
cd backend/public
php -S 127.0.0.1:8080
```

### 2. 启动后台管理系统

```bash
cd admin
npm run dev
```

访问地址：http://localhost:8083

### 3. 启动小程序前端（H5模式）

```bash
cd miniprogram
npm run dev:h5
```

访问地址：http://localhost:5173

### 4. 构建小程序

```bash
cd miniprogram
npm run build:mp-weixin
```

构建结果：`dist/build/mp-weixin`

## 功能说明

### 后台管理系统

- **首页概览**：系统数据统计
- **分类管理**：管理剧本分类
- **品牌管理**：管理品牌信息，支持Logo上传
- **剧本管理**：管理剧本信息，支持封面上传和审核
- **市集管理**：管理市集内容
- **首页内容管理**：管理轮播图和广告位
- **施工案例权限管理**：管理用户的施工案例权限申请
- **施工案例管理**：管理用户提交的施工案例
- **社区管理**：管理用户发布的帖子
- **评论管理**：管理用户发布的评论

### 小程序前端

- **首页**：轮播图、推荐剧本、分类入口
- **分类**：按分类浏览剧本
- **品牌**：品牌列表和详情
- **市集**：市集内容浏览
- **社区**：用户发帖和评论
- **我的**：
  - 个人信息
  - 发布剧本（仅品牌方可见）
  - 我的剧本
  - 我的施工案例
  - 权限申请

## API端点

### 前端API
- **GET /api/home** - 首页数据
- **GET /api/categories** - 分类列表
- **GET /api/categories/{id}/scripts** - 分类剧本列表
- **GET /api/brands** - 品牌列表
- **GET /api/brands/{id}** - 品牌详情
- **GET /api/scripts/search** - 剧本搜索
- **GET /api/scripts/{id}** - 剧本详情
- **GET /api/construction-cases** - 施工案例列表
- **GET /api/construction-cases/{id}** - 施工案例详情
- **GET /api/market** - 市集数据
- **GET /api/community/posts** - 社区帖子列表
- **GET /api/community/posts/{id}** - 社区帖子详情
- **POST /api/construction-case-permission** - 提交施工案例权限申请
- **GET /api/user/construction-case-permission** - 获取用户权限状态
- **POST /api/scripts** - 发布剧本（品牌方）
- **GET /api/user/scripts** - 获取用户剧本列表

### 后台API
- **POST /api/admin/login** - 后台登录
- **GET /api/admin/categories** - 后台分类列表
- **GET /api/admin/brands** - 后台品牌列表
- **GET /api/admin/scripts** - 后台剧本列表
- **GET /api/admin/construction-permissions** - 后台权限申请列表
- **GET /api/admin/construction-cases** - 后台施工案例列表
- **GET /api/admin/community/posts** - 后台社区帖子列表
- **GET /api/admin/community/comments** - 后台评论列表

## 默认账号

### 后台管理系统
- 用户名：admin
- 密码：admin123

## 注意事项

1. 确保本地环境的PHP、MySQL和Node.js版本符合要求
2. 数据库连接信息必须与配置一致
3. 后端服务必须在8080端口运行
4. 上传的图片会保存在 `backend/public/uploads` 目录

## 项目维护

### 数据库备份

定期备份数据库，确保数据安全。

### 代码更新

- 后端：直接修改 `backend/public/index.php` 文件
- 前端：修改相应的Vue组件文件

### 依赖更新

```bash
# 后端
cd backend
composer update

# 前端
cd admin
npm update

cd miniprogram
npm update
```

## 常见问题

### 1. 404 API端点未找到
- 检查后端服务是否正常运行
- 检查API路径是否正确

### 2. 数据库连接错误
- 检查数据库配置是否正确
- 检查MySQL服务是否运行

### 3. 图片上传失败
- 检查 `backend/public/uploads` 目录是否存在且可写
- 检查文件大小是否超过限制

### 4. 权限申请不显示在品牌管理中
- 权限申请通过后会自动创建品牌记录
- 检查品牌管理页面的状态筛选

## 总结

密室逃脱剧本展示系统是一个功能完整的行业管理平台，通过前后端分离的架构，为用户提供了良好的使用体验。系统支持品牌方发布剧本、施工方申请权限、用户浏览内容等功能，满足了密室逃脱行业的多种需求。