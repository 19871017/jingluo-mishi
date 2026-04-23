<template>
  <div class="script-page">
    <el-card>
      <template #header>
        <div class="card-header">
          <div>
            <div class="header-title">剧本管理</div>
            <div class="header-desc">
              {{
                store.isBrandAdmin
                  ? '品牌方可在这里维护自己的剧本资料，提交后会进入平台审核。'
                  : '管理员可在这里管理全站剧本并审核品牌方提交的内容。'
              }}
            </div>
          </div>

          <div class="header-actions">
            <el-form :inline="true" :model="searchForm">
              <el-form-item label="状态">
                <el-select v-model="searchForm.status" clearable placeholder="全部状态" style="width: 150px">
                  <el-option label="草稿" value="draft" />
                  <el-option label="待审核" value="pending" />
                  <el-option label="已通过" value="approved" />
                  <el-option label="已拒绝" value="rejected" />
                </el-select>
              </el-form-item>
              <el-form-item label="完整度">
                <el-select v-model="searchForm.completeness" clearable placeholder="全部资料" style="width: 160px">
                  <el-option label="资料不完整" value="incomplete" />
                  <el-option label="资料完整" value="complete" />
                </el-select>
              </el-form-item>
              <el-form-item>
                <el-button type="primary" @click="handleSearch">筛选</el-button>
              </el-form-item>
              <el-form-item>
                <el-button @click="resetSearch">重置筛选</el-button>
              </el-form-item>
            </el-form>
            <el-button type="success" @click="openDialog()">新增剧本</el-button>
          </div>
        </div>
      </template>

      <el-table :data="list" border>
        <el-table-column prop="id" label="ID" width="70" />
        <el-table-column prop="name" label="剧本名称" min-width="180" />
        <el-table-column label="品牌" min-width="140">
          <template #default="{ row }">{{ brandName(row.brand_id) }}</template>
        </el-table-column>
        <el-table-column label="剧本类目" min-width="120">
          <template #default="{ row }">{{ categoryName(row.category_id) }}</template>
        </el-table-column>
        <el-table-column prop="script_type" label="类型" min-width="120" />
        <el-table-column label="人数" width="110">
          <template #default="{ row }">{{ row.min_players }} - {{ row.max_players }}</template>
        </el-table-column>
        <el-table-column prop="duration" label="时长(分钟)" width="110" />
        <el-table-column label="首页轮播" width="110">
          <template #default="{ row }">
            <el-tag v-if="Number(row.is_home_featured) > 0" type="success" effect="plain">已置顶</el-tag>
            <el-tag v-else effect="plain">未置顶</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="home_featured_sort" label="轮播排序" width="100" />
        <el-table-column label="剧本页轮播" width="120">
          <template #default="{ row }">
            <el-tag v-if="Number(row.is_script_featured) > 0" type="warning" effect="plain">已置顶</el-tag>
            <el-tag v-else effect="plain">未置顶</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="script_featured_sort" label="剧本页排序" width="110" />
        <el-table-column label="数据统计" min-width="220">
          <template #default="{ row }">
            <div class="stats-summary">
              <span>浏览 {{ row.view_count || 0 }}</span>
              <span>点赞 {{ row.like_count || 0 }}</span>
              <span>收藏 {{ row.collect_count || 0 }}</span>
              <span>已购 {{ row.purchase_count || 0 }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="资料完整度" min-width="240">
          <template #default="{ row }">
            <div class="completeness-tags">
              <el-tag v-for="item in incompleteFields(row)" :key="item" type="warning" effect="plain">{{ item }}</el-tag>
              <el-tag v-if="!incompleteFields(row).length" type="success" effect="plain">已完整</el-tag>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="statusType(row.status)">{{ statusText(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="260" fixed="right">
          <template #default="{ row }">
            <el-button
              v-if="!store.isBrandAdmin && row.status === 'pending'"
              type="success"
              link
              @click="handleAudit(row, 'approved')"
            >
              通过
            </el-button>
            <el-button
              v-if="!store.isBrandAdmin && row.status === 'pending'"
              type="danger"
              link
              @click="handleAudit(row, 'rejected')"
            >
              拒绝
            </el-button>
            <el-button type="primary" link @click="openDialog(row)">编辑</el-button>
            <el-button type="danger" link @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="script-summary-bar">
        <el-tag effect="plain">共 {{ total }} 条剧本</el-tag>
        <el-tag type="warning" effect="plain">资料不完整 {{ incompleteCount }} 条</el-tag>
        <el-tag type="success" effect="plain">资料完整 {{ completeCount }} 条</el-tag>
      </div>

      <el-pagination
        v-model:current-page="page"
        style="margin-top: 20px"
        background
        layout="total, prev, pager, next"
        :total="total"
        @current-change="fetchList"
      />
    </el-card>

    <el-dialog v-model="dialogVisible" :title="currentId ? '编辑剧本' : '新增剧本'" width="980px">
      <el-form :model="form" label-width="120px">
        <el-row :gutter="16">
          <el-col :span="12">
            <el-form-item label="剧本名称">
              <el-input v-model="form.name" placeholder="请输入剧本名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item v-if="!store.isBrandAdmin" label="所属品牌">
              <el-select v-model="form.brand_id" placeholder="请选择品牌" style="width: 100%">
                <el-option v-for="item in brands" :key="item.id" :label="item.name" :value="item.id" />
              </el-select>
            </el-form-item>
            <el-form-item v-else label="所属品牌">
              <el-input :model-value="store.adminInfo?.brand_name || '未绑定品牌'" disabled />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="16">
          <el-col :span="12">
            <el-form-item label="剧本类目">
              <el-select v-model="form.category_id" placeholder="请选择剧本类目" style="width: 100%">
                <el-option v-for="item in categories" :key="item.id" :label="item.name" :value="item.id" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="剧本类型">
              <el-select
                v-model="form.script_type"
                filterable
                placeholder="建议与剧本类目及高级筛选保持一致"
                style="width: 100%"
              >
                <el-option v-for="item in scriptTypeOptions" :key="item" :label="item" :value="item" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="16">
          <el-col :span="8">
            <el-form-item label="最少人数">
              <el-input-number v-model="form.min_players" :min="1" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="最多人数">
              <el-input-number v-model="form.max_players" :min="1" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="时长(分钟)">
              <el-input-number v-model="form.duration" :min="1" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="16">
          <el-col :span="8">
            <el-form-item label="恐怖等级">
              <el-input-number v-model="form.horror_level" :min="0" :max="5" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="难度">
              <el-select v-model="form.difficulty" placeholder="请选择难度" style="width: 100%">
                <el-option v-for="item in difficultyOptions" :key="item" :label="item" :value="item" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="密室大小">
              <el-select v-model="form.room_size" placeholder="请选择密室大小" style="width: 100%">
                <el-option v-for="item in roomSizeOptions" :key="item" :label="item" :value="item" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="16">
          <el-col :span="8">
            <el-form-item label="面积(㎡)">
              <el-input-number v-model="form.area_size" :min="0" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="房间数量">
              <el-select v-model="form.room_count" allow-create filterable placeholder="请选择" style="width: 100%">
                <el-option v-for="item in countOptions" :key="item" :label="item" :value="item" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="走廊数量">
              <el-select v-model="form.corridor_count" allow-create filterable placeholder="请选择" style="width: 100%">
                <el-option v-for="item in countOptions" :key="item" :label="item" :value="item" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="16">
          <el-col :span="8">
            <el-form-item label="滚场">
              <el-select v-model="form.rotation_count" allow-create filterable placeholder="请选择" style="width: 100%">
                <el-option v-for="item in rotationOptions" :key="item" :label="item" :value="item" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="NPC 数量">
              <el-select v-model="form.npc_count" allow-create filterable placeholder="请选择" style="width: 100%">
                <el-option v-for="item in countOptions" :key="item" :label="item" :value="item" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="授权状态">
              <el-select v-model="form.auth_status" placeholder="请选择授权状态" style="width: 100%">
                <el-option v-for="item in authStatusOptions" :key="item" :label="item" :value="item" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item v-if="!store.isBrandAdmin" label="状态">
          <el-select v-model="form.status" placeholder="请选择状态" style="width: 220px">
            <el-option label="草稿" value="draft" />
            <el-option label="待审核" value="pending" />
            <el-option label="已通过" value="approved" />
            <el-option label="已拒绝" value="rejected" />
          </el-select>
        </el-form-item>
        <el-form-item v-else label="提交说明">
          <el-alert title="品牌方新增或编辑剧本后会自动进入待审核状态。" type="info" :closable="false" />
        </el-form-item>

        <el-form-item label="特色标签">
          <el-checkbox-group v-model="form.feature_tags">
            <el-checkbox v-for="item in featureOptions" :key="item" :label="item">{{ item }}</el-checkbox>
          </el-checkbox-group>
        </el-form-item>

        <el-form-item label="适合玩家">
          <el-checkbox-group v-model="form.suitable_players">
            <el-checkbox v-for="item in suitablePlayerOptions" :key="item" :label="item">{{ item }}</el-checkbox>
          </el-checkbox-group>
        </el-form-item>

        <el-form-item label="授权服务">
          <el-checkbox-group v-model="form.auth_services">
            <el-checkbox v-for="item in authServiceOptions" :key="item" :label="item">{{ item }}</el-checkbox>
          </el-checkbox-group>
        </el-form-item>

        <el-form-item label="可授权城市">
          <el-select v-model="form.auth_cities" multiple filterable placeholder="请选择城市" style="width: 100%">
            <el-option-group
              v-for="group in provinceCityGroups"
              :key="group.province"
              :label="group.province"
            >
              <el-option v-for="item in group.cities" :key="item" :label="item" :value="item" />
            </el-option-group>
          </el-select>
        </el-form-item>

        <el-form-item label="已授权城市">
          <el-select v-model="form.authorized_cities" multiple filterable placeholder="请选择城市" style="width: 100%">
            <el-option-group
              v-for="group in provinceCityGroups"
              :key="group.province"
              :label="group.province"
            >
              <el-option v-for="item in group.cities" :key="item" :label="item" :value="item" />
            </el-option-group>
          </el-select>
        </el-form-item>

        <el-row :gutter="16">
          <el-col :span="12">
            <el-form-item label="授权方">
              <el-input v-model="form.authorizer" placeholder="例如：品牌总部 / 版权方" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="授权价格">
              <div ref="priceFieldRef" style="width: 100%">
                <el-input-number v-model="form.price_tier1" :min="0" :precision="2" style="width: 100%" />
              </div>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="首页轮播置顶">
          <el-switch v-model="form.is_home_featured" :active-value="1" :inactive-value="0" :disabled="store.isBrandAdmin" />
          <span class="switch-help">置顶后，该剧本封面会优先作为首页轮播图展示，点击可直接进入详情。</span>
        </el-form-item>

        <el-form-item label="轮播排序值">
          <el-input-number v-model="form.home_featured_sort" :min="0" :disabled="store.isBrandAdmin || !form.is_home_featured" style="width: 220px" />
          <span class="switch-help">数字越小越靠前。建议第 1 张设为 1，第 2 张设为 2，第 3 张设为 3。</span>
        </el-form-item>

        <el-form-item label="剧本页轮播置顶">
          <el-switch v-model="form.is_script_featured" :active-value="1" :inactive-value="0" :disabled="store.isBrandAdmin" />
          <span class="switch-help">置顶后，该剧本封面会优先作为“剧本”页轮播图展示。</span>
        </el-form-item>

        <el-form-item label="剧本页排序值">
          <el-input-number v-model="form.script_featured_sort" :min="0" :disabled="store.isBrandAdmin || !form.is_script_featured" style="width: 220px" />
          <span class="switch-help">数字越小越靠前，用于控制“剧本”页轮播顺序。</span>
        </el-form-item>

        <el-row :gutter="16">
          <el-col :span="6">
            <el-form-item label="浏览数量">
              <el-input-number v-model="form.view_count" :min="0" :disabled="store.isBrandAdmin" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="点赞数量">
              <el-input-number v-model="form.like_count" :min="0" :disabled="store.isBrandAdmin" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="收藏数量">
              <el-input-number v-model="form.collect_count" :min="0" :disabled="store.isBrandAdmin" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="已购买数量">
              <el-input-number v-model="form.purchase_count" :min="0" :disabled="store.isBrandAdmin" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="封面图片">
          <el-upload :http-request="uploadCover" :show-file-list="false" accept=".jpg,.jpeg,.png,.gif,.webp">
            <el-button type="primary">上传封面</el-button>
          </el-upload>
          <el-input v-model="form.cover_image" placeholder="上传后自动回填，也可以手动填写图片地址" style="margin-top: 12px" />
          <el-image v-if="form.cover_image" :src="form.cover_image" class="cover-preview" fit="cover" />
        </el-form-item>

        <el-form-item label="图集图片">
          <div ref="galleryFieldRef" class="gallery-editor">
            <div class="gallery-toolbar">
              <el-upload :http-request="uploadGalleryImage" :show-file-list="false" accept=".jpg,.jpeg,.png,.gif,.webp">
                <el-button type="primary" plain>追加图片</el-button>
              </el-upload>
              <el-button text type="danger" @click="form.gallery_images = []">清空图集</el-button>
            </div>
            <div class="gallery-list">
              <div v-for="(image, index) in form.gallery_images" :key="`${image}-${index}`" class="gallery-item">
                <el-image :src="image" fit="cover" class="gallery-thumb" />
                <div class="gallery-actions">
                  <el-button size="small" text type="danger" @click="removeGalleryImage(index)">删除</el-button>
                </div>
              </div>
            </div>
          </div>
        </el-form-item>

        <el-form-item label="视频地址">
          <div ref="videoFieldRef" style="width: 100%">
            <div class="video-toolbar">
              <el-upload :http-request="uploadVideo" :show-file-list="false" accept=".mp4,.mov,.webm">
                <el-button type="primary" plain>上传视频</el-button>
              </el-upload>
            </div>
            <el-input v-model="form.video_url" placeholder="请输入视频 URL，用于剧本详情页播放" />
          </div>
        </el-form-item>

        <el-form-item label="图文简介">
          <el-input v-model="form.description" type="textarea" :rows="4" placeholder="用于列表页和详情页首屏展示" />
        </el-form-item>

        <el-form-item label="详细介绍">
          <div ref="detailFieldRef" style="width: 100%">
            <el-input
              v-model="form.detail_content"
              type="textarea"
              :rows="6"
              placeholder="可补充世界观、亮点机关、玩法说明、演绎说明等内容"
            />
          </div>
        </el-form-item>

        <el-form-item label="前台详情预览">
          <div class="preview-panel">
            <div class="preview-alerts">
              <el-tag v-for="item in incompleteFormFields" :key="item" type="warning" effect="plain" class="preview-alert-tag" @click="scrollToMissingField(item)">{{ item }}</el-tag>
              <el-tag v-if="!incompleteFormFields.length" type="success" effect="plain">资料已完整，可直接提交</el-tag>
            </div>

            <div class="preview-gallery">
              <el-image
                v-for="(image, index) in previewImages"
                :key="`${image}-${index}`"
                :src="image"
                fit="cover"
                class="preview-gallery-image"
              />
              <div v-if="!previewImages.length" class="preview-empty-media">暂无图集，前台会显示默认占位</div>
            </div>

            <div class="preview-hero">
              <div>
                <div class="preview-caption">剧本详情效果预览</div>
                <div class="preview-title">{{ form.name || '未填写剧本名称' }}</div>
              </div>
              <div class="preview-heat">{{ form.like_count || 0 }} 热度</div>
            </div>

            <div class="preview-description">{{ form.description || '暂无剧本介绍' }}</div>

            <div class="preview-tags">
              <span class="preview-tag strong">{{ form.script_type || '待补充类型' }}</span>
              <span class="preview-tag">{{ form.min_players || 1 }}-{{ form.max_players || 1 }} 人</span>
              <span class="preview-tag">{{ form.duration || 0 }} 分钟</span>
              <span class="preview-tag">{{ Number(form.price_tier1 || 0) > 0 ? `￥${form.price_tier1}` : '价格待补充' }}</span>
            </div>

            <div class="preview-stats-grid">
              <div class="preview-info-card">
                <div class="preview-info-label">浏览数量</div>
                <div class="preview-info-value">{{ form.view_count || 0 }}</div>
              </div>
              <div class="preview-info-card">
                <div class="preview-info-label">点赞数量</div>
                <div class="preview-info-value">{{ form.like_count || 0 }}</div>
              </div>
              <div class="preview-info-card">
                <div class="preview-info-label">收藏数量</div>
                <div class="preview-info-value">{{ form.collect_count || 0 }}</div>
              </div>
              <div class="preview-info-card">
                <div class="preview-info-label">已购买数量</div>
                <div class="preview-info-value">{{ form.purchase_count || 0 }}</div>
              </div>
            </div>

            <div class="preview-info-grid">
              <div class="preview-info-card">
                <div class="preview-info-label">视频展示</div>
                <div class="preview-info-value">{{ form.video_url ? '已填写，前台会显示视频区块' : '未填写，前台会显示暂无视频展示' }}</div>
              </div>
              <div class="preview-info-card">
                <div class="preview-info-label">详细介绍</div>
                <div class="preview-info-value">{{ form.detail_content ? '已填写，前台会显示图文介绍' : '未填写，前台会显示暂无详细介绍' }}</div>
              </div>
            </div>

            <div class="preview-section">
              <div class="preview-section-title">详细介绍内容</div>
              <div class="preview-section-body">{{ form.detail_content || '该剧本暂未补充图文说明、世界观或玩法细节，后续完善后会展示在这里。' }}</div>
            </div>
          </div>
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { brandApi, categoryApi, metaApi, scriptApi, uploadApi } from '../../api'
import { useStore } from '../../store'
import { SCRIPT_TAXONOMY } from '../../constants/taxonomy'

const countOptions = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '10+']
const rotationOptions = ['不可滚场', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '10+']
const scriptTypeOptions = SCRIPT_TAXONOMY
const difficultyOptions = ['简单', '中等难度', '烧脑']
const roomSizeOptions = ['小型密室', '中型密室', '大型密室', '巨型密室']
const authStatusOptions = ['不可授权', '可授权']
const featureOptions = ['角色扮演', '武侠', '全息投影', '械战', '韩式', '玄幻', 'VR', '有换装', '二次元', '欧式', '穿越', '情感', '多支线', '运动量大', '魔法', '机械', '科幻', '单人任务', '港风', '日式', '追逐', '对抗', '校园', '无换装', '大型机械', '悬疑', '美式', '有剧情', '宫廷', '古风']
const suitablePlayerOptions = ['情侣约会', '学生组队', '团建聚会', '新手玩家', '硬核解谜', '戏精演绎', '亲子家庭', '高玩挑战', '恐怖爱好者', '社牛玩家', '微恐体验', '剧情控玩家']
const authServiceOptions = ['主题详情', '主题流程', '图片资料', '音频资料', '平面图纸', '机关清单', '店员培训', '强弱电图纸', '海报', '宣传视频']

const store = useStore()
const list = ref([])
const total = ref(0)
const page = ref(1)
const brands = ref([])
const categories = ref([])
const provinceCityGroups = ref([])
const dialogVisible = ref(false)
const currentId = ref(null)
const priceFieldRef = ref(null)
const galleryFieldRef = ref(null)
const videoFieldRef = ref(null)
const detailFieldRef = ref(null)
const searchForm = reactive({ status: '', completeness: '' })
const form = reactive(createDefaultForm())
const completenessOrder = ['缺图集', '缺价格', '缺详情', '缺视频']

const incompleteCount = computed(() => list.value.filter((row) => incompleteFields(row).length).length)
const completeCount = computed(() => list.value.filter((row) => !incompleteFields(row).length).length)
const previewImages = computed(() => {
  const images = Array.isArray(form.gallery_images) ? form.gallery_images.filter(Boolean) : []
  if (images.length) return images
  return form.cover_image ? [form.cover_image] : []
})
const incompleteFormFields = computed(() => incompleteFields(form))

function createDefaultForm() {
  return {
    name: '',
    brand_id: store.brandId || null,
    category_id: null,
    min_players: 2,
    max_players: 8,
    duration: 120,
    status: 'draft',
    view_count: 0,
    like_count: 0,
    collect_count: 0,
    purchase_count: 0,
    is_home_featured: 0,
    home_featured_sort: 0,
    is_script_featured: 0,
    script_featured_sort: 0,
    cover_image: '',
    description: '',
    script_type: '',
    horror_level: 0,
    difficulty: '',
    room_size: '',
    feature_tags: [],
    area_size: 0,
    room_count: '',
    rotation_count: '',
    npc_count: '',
    corridor_count: '',
    suitable_players: [],
    auth_status: '',
    auth_services: [],
    authorized_cities: [],
    auth_cities: [],
    gallery_images: [],
    video_url: '',
    detail_content: '',
    authorizer: '',
    price_tier1: 0,
  }
}

function resetForm() {
  Object.assign(form, createDefaultForm())
}

function statusText(status) {
  return {
    draft: '草稿',
    pending: '待审核',
    approved: '已通过',
    rejected: '已拒绝',
  }[status] || status
}

function statusType(status) {
  return {
    draft: 'info',
    pending: 'warning',
    approved: 'success',
    rejected: 'danger',
  }[status] || 'info'
}

function brandName(id) {
  return brands.value.find((item) => Number(item.id) === Number(id))?.name || `品牌 #${id}`
}

function categoryName(id) {
  return categories.value.find((item) => Number(item.id) === Number(id))?.name || `剧本类目 #${id}`
}

function incompleteFields(row) {
  const fields = []

  if (!String(row.video_url || '').trim()) {
    fields.push('缺视频')
  }

  if (!String(row.detail_content || '').trim()) {
    fields.push('缺详情')
  }

  if (Number(row.price_tier1 || 0) <= 0) {
    fields.push('缺价格')
  }

  const galleryImages = Array.isArray(row.gallery_images) ? row.gallery_images.filter(Boolean) : []
  if (!galleryImages.length) {
    fields.push('缺图集')
  }

  return fields.sort((a, b) => completenessOrder.indexOf(a) - completenessOrder.indexOf(b))
}

function scrollToMissingField(label) {
  const map = {
    缺价格: priceFieldRef.value,
    缺图集: galleryFieldRef.value,
    缺视频: videoFieldRef.value,
    缺详情: detailFieldRef.value,
  }

  map[label]?.scrollIntoView?.({ behavior: 'smooth', block: 'center' })
}

async function fetchBrands() {
  const data = await brandApi.list({ page: 1, limit: 100, status: 'approved' })
  brands.value = data.list || []
}

async function fetchCategories() {
  const data = await categoryApi.list()
  categories.value = Array.isArray(data) ? data : []
}

function syncCategoryWithType() {
  if (!form.script_type || !Array.isArray(categories.value) || !categories.value.length) {
    return
  }

  const matchedCategory = categories.value.find((item) => item.name === form.script_type)
  if (matchedCategory) {
    form.category_id = matchedCategory.id
  }
}

async function fetchCityRegions() {
  const data = await metaApi.getCities()
  provinceCityGroups.value = Array.isArray(data.list) ? data.list : []
}

async function fetchList() {
  const data = await scriptApi.list({ page: page.value, limit: 20, status: searchForm.status, completeness: searchForm.completeness })
  list.value = data.list || []
  total.value = data.total || 0
}

function handleSearch() {
  page.value = 1
  fetchList()
}

function resetSearch() {
  searchForm.status = ''
  searchForm.completeness = ''
  page.value = 1
  fetchList()
}

function openDialog(row = null) {
  currentId.value = row?.id || null

  if (row) {
    Object.assign(form, createDefaultForm(), {
      ...row,
      brand_id: row.brand_id,
      category_id: row.category_id,
      feature_tags: Array.isArray(row.feature_tags) ? row.feature_tags : [],
      suitable_players: Array.isArray(row.suitable_players) ? row.suitable_players : [],
      auth_services: Array.isArray(row.auth_services) ? row.auth_services : [],
      authorized_cities: Array.isArray(row.authorized_cities) ? row.authorized_cities : [],
      auth_cities: Array.isArray(row.auth_cities) ? row.auth_cities : [],
      gallery_images: Array.isArray(row.gallery_images) ? row.gallery_images : [],
      view_count: Number(row.view_count || 0),
      like_count: Number(row.like_count || 0),
      collect_count: Number(row.collect_count || 0),
      purchase_count: Number(row.purchase_count || 0),
      is_home_featured: Number(row.is_home_featured || 0),
      home_featured_sort: Number(row.home_featured_sort || 0),
      is_script_featured: Number(row.is_script_featured || 0),
      script_featured_sort: Number(row.script_featured_sort || 0),
      price_tier1: Number(row.price_tier1 || 0),
    })
  } else {
    resetForm()
  }

  dialogVisible.value = true
}

async function uploadCover(options) {
  try {
    const payload = await uploadApi.image(options.file)
    form.cover_image = payload?.url || payload?.data?.url || ''
    if (form.cover_image && !form.gallery_images.includes(form.cover_image)) {
      form.gallery_images.unshift(form.cover_image)
    }
    ElMessage.success('封面上传成功')
    options.onSuccess?.(payload)
  } catch (error) {
    console.error(error)
    ElMessage.error('封面上传失败')
    options.onError?.(error)
  }
}

async function uploadGalleryImage(options) {
  try {
    const payload = await uploadApi.image(options.file)
    const url = payload?.url || payload?.data?.url || ''
    if (url) {
      form.gallery_images.push(url)
      ElMessage.success('图集图片上传成功')
    }
    options.onSuccess?.(payload)
  } catch (error) {
    console.error(error)
    ElMessage.error('图集图片上传失败')
    options.onError?.(error)
  }
}

async function uploadVideo(options) {
  try {
    const payload = await uploadApi.video(options.file)
    const url = payload?.url || payload?.data?.url || ''
    if (url) {
      form.video_url = url
      ElMessage.success('视频上传成功')
    }
    options.onSuccess?.(payload)
  } catch (error) {
    console.error(error)
    ElMessage.error('视频上传失败')
    options.onError?.(error)
  }
}

function removeGalleryImage(index) {
  form.gallery_images.splice(index, 1)
}

async function handleSubmit() {
  if (!form.name || !form.category_id || (!store.isBrandAdmin && !form.brand_id)) {
    ElMessage.warning('请先补全剧本名称、品牌和剧本类目')
    return
  }

  if (store.isBrandAdmin && incompleteFormFields.value.length) {
    try {
      await ElMessageBox.confirm(
        `当前仍有未完善资料：${incompleteFormFields.value.join('、')}。继续保存后会进入待审核状态，确定继续吗？`,
        '资料未完善提醒',
        {
          type: 'warning',
          confirmButtonText: '继续保存',
          cancelButtonText: '返回完善',
        }
      )
    } catch {
      return
    }
  }

  const payload = {
    ...form,
    brand_id: store.isBrandAdmin ? store.brandId : form.brand_id,
    gallery_images: Array.from(new Set([...(form.gallery_images || []), form.cover_image].filter(Boolean))),
  }

  try {
    if (currentId.value) {
      await scriptApi.update(currentId.value, payload)
      ElMessage.success(store.isBrandAdmin ? '剧本已更新并重新提交审核' : '剧本已更新')
    } else {
      await scriptApi.create(payload)
      ElMessage.success(store.isBrandAdmin ? '剧本已提交审核' : '剧本已创建')
    }

    dialogVisible.value = false
    await fetchList()
  } catch (error) {
    console.error(error)
  }
}

async function handleDelete(row) {
  try {
    await ElMessageBox.confirm(`确定删除剧本“${row.name}”吗？`, '删除确认', { type: 'warning' })
    await scriptApi.delete(row.id)
    ElMessage.success('删除成功')
    await fetchList()
  } catch (error) {
    if (error !== 'cancel') {
      console.error(error)
    }
  }
}

async function handleAudit(row, status) {
  try {
    await scriptApi.audit(row.id, status)
    ElMessage.success('审核成功')
    await fetchList()
  } catch (error) {
    console.error(error)
  }
}

onMounted(async () => {
  resetForm()
  const tasks = [fetchCategories(), fetchCityRegions(), fetchList()]
  if (!store.isBrandAdmin) {
    tasks.push(fetchBrands())
  }
  await Promise.all(tasks)
})

watch(() => form.script_type, () => {
  syncCategoryWithType()
})

watch(categories, () => {
  syncCategoryWithType()
})
</script>

<style scoped>
.card-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-title {
  font-size: 18px;
  font-weight: 700;
  color: #303133;
}

.header-desc {
  margin-top: 6px;
  font-size: 13px;
  color: #909399;
}

.script-summary-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 14px;
}

.stats-summary {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  color: #4b5563;
  line-height: 1.8;
}

.switch-help {
  margin-left: 10px;
  color: #6b7280;
  font-size: 13px;
}

.preview-panel {
  width: 100%;
  padding: 18px;
  border-radius: 18px;
  background: linear-gradient(180deg, #fff7ef, #ffffff);
  border: 1px solid rgba(249, 115, 22, 0.08);
}

.preview-alerts {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 14px;
}

.preview-alert-tag {
  cursor: pointer;
}

.preview-gallery {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}

.preview-gallery-image {
  width: 100%;
  height: 140px;
  border-radius: 12px;
}

.preview-empty-media {
  grid-column: 1 / -1;
  padding: 18px;
  border-radius: 12px;
  background: #f8fafc;
  color: #6b7280;
  text-align: center;
}

.preview-hero {
  margin-top: 18px;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.preview-caption {
  font-size: 12px;
  color: #9ca3af;
}

.preview-title {
  margin-top: 6px;
  font-size: 24px;
  font-weight: 800;
  color: #1f2937;
}

.preview-heat {
  padding: 8px 12px;
  border-radius: 999px;
  background: #fff1de;
  color: #f97316;
  font-weight: 700;
}

.preview-description {
  margin-top: 14px;
  color: #6b7280;
  line-height: 1.8;
}

.preview-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 14px;
}

.preview-tag {
  padding: 8px 12px;
  border-radius: 12px;
  background: #f3f4f6;
  color: #4b5563;
  font-size: 13px;
}

.preview-tag.strong {
  background: #fff1de;
  color: #f97316;
  font-weight: 700;
}

.preview-info-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  margin-top: 16px;
}

.preview-info-grid.compact {
  margin-top: 16px;
}

.preview-stats-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
  margin-top: 16px;
}

.preview-stats-grid .preview-info-card {
  text-align: center;
  background: linear-gradient(180deg, #fffaf5, #ffffff);
  border-color: rgba(249, 115, 22, 0.1);
}

.preview-stats-grid .preview-info-value {
  font-size: 20px;
  font-weight: 800;
  color: #f97316;
}

.preview-info-card {
  padding: 14px;
  border-radius: 14px;
  background: #ffffff;
  border: 1px solid rgba(148, 163, 184, 0.12);
}

.preview-info-label {
  font-size: 12px;
  color: #9ca3af;
}

.preview-info-value {
  margin-top: 6px;
  color: #374151;
  line-height: 1.6;
}

.preview-section {
  margin-top: 16px;
  padding: 14px;
  border-radius: 14px;
  background: #ffffff;
  border: 1px solid rgba(148, 163, 184, 0.12);
}

.preview-section-title {
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
}

.preview-section-body {
  margin-top: 10px;
  color: #4b5563;
  line-height: 1.8;
  white-space: pre-wrap;
}

.completeness-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.cover-preview {
  width: 160px;
  height: 120px;
  margin-top: 12px;
  border-radius: 10px;
  border: 1px solid #ebeef5;
}

.gallery-editor {
  width: 100%;
}

.video-toolbar {
  margin-bottom: 12px;
}

.gallery-toolbar {
  display: flex;
  align-items: center;
  gap: 12px;
}

.gallery-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 12px;
  margin-top: 12px;
}

.gallery-item {
  border: 1px solid #ebeef5;
  border-radius: 12px;
  overflow: hidden;
  background: #fff;
}

.gallery-thumb {
  width: 100%;
  height: 120px;
  display: block;
}

.gallery-actions {
  display: flex;
  justify-content: flex-end;
  padding: 8px 10px;
}
</style>
