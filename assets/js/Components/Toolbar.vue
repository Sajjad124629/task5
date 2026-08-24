<template>
  <div class="card border-0 shadow-sm mb-4 bg-white rounded-3">
    <div class="card-body py-3 px-3">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex flex-wrap align-items-center gap-3 flex-grow-1">
          <div style="min-width: 170px;">
            <label class="form-label text-xs font-medium text-muted mb-1 d-block">Language</label>
            <select 
              :value="locale" 
              @change="updateParam('locale', $event.target.value)" 
              class="form-select form-select-sm border rounded-2 font-medium"
            >
              <option v-for="(label, key) in locales" :key="key" :value="key">
                {{ label }}
              </option>
            </select>
          </div>

          <div style="min-width: 190px;">
            <label class="form-label text-xs font-medium text-muted mb-1 d-block">Seed</label>
            <div class="input-group input-group-sm">
              <input 
                type="text" 
                :value="seed" 
                @input="updateParam('seed', $event.target.value)" 
                class="form-control form-control-sm font-monospace border-end-0" 
                placeholder="Seed value..."
              />
              <button 
                @click="generateRandomSeed" 
                class="btn btn-outline-secondary border-start-0 d-flex align-items-center justify-content-center px-2" 
                type="button"
                title="Random Seed"
              >
                <iconify-icon icon="solar:shuffle-bold" width="16"></iconify-icon>
              </button>
            </div>
          </div>

          <div class="flex-grow-1" style="min-width: 180px; max-width: 260px;">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label text-xs font-medium text-muted mb-0">Likes</label>
              <span class="text-xs font-monospace text-primary fw-bold">{{ likes }}</span>
            </div>
            <input 
              type="range" 
              min="0" 
              max="10" 
              step="0.1" 
              :value="likes" 
              @input="updateParam('likes', parseFloat($event.target.value))" 
              class="form-range custom-range" 
            />
          </div>

          <div style="width: 110px;">
            <label class="form-label text-xs font-medium text-muted mb-1 d-block">Review</label>
            <input 
              type="number" 
              min="0" 
              max="10" 
              step="0.1" 
              :value="reviews" 
              @input="updateParam('reviews', parseFloat($event.target.value) || 0)" 
              class="form-control form-control-sm text-center font-monospace border rounded-2"
            />
          </div>
        </div>

        <div class="d-flex align-items-center gap-2">
          <div class="btn-group p-1 bg-light rounded-3 border" role="group">
            <button 
              @click="updateParam('view', 'table')" 
              class="btn btn-sm border-0 rounded-2 d-flex align-items-center p-2" 
              :class="view === 'table' ? 'btn-primary shadow-xs' : 'text-secondary hover-bg-white'"
              title="Table View"
            >
              <iconify-icon icon="solar:hamburger-menu-bold" width="18"></iconify-icon>
            </button>
            <button 
              @click="updateParam('view', 'gallery')" 
              class="btn btn-sm border-0 rounded-2 d-flex align-items-center p-2" 
              :class="view === 'gallery' ? 'btn-primary shadow-xs' : 'text-secondary hover-bg-white'"
              title="Gallery View"
            >
              <iconify-icon icon="solar:widget-2-bold" width="18"></iconify-icon>
            </button>
          </div>

          <a :href="exportUrl" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 font-medium px-3 py-2 rounded-2" title="Export Current Batch as ZIP">
            <iconify-icon icon="solar:download-minimalistic-bold" width="16"></iconify-icon>
            <span class="d-none d-sm-inline">Export</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  seed: [String, Number],
  locale: String,
  likes: Number,
  reviews: Number,
  view: String,
  locales: Object,
  page: Number
});

const emit = defineEmits(['update:param']);

function updateParam(key, val) {
  emit('update:param', { key, val });
}

function generateRandomSeed() {
  // Generate random 48-bit style integer
  const randomSeed = Math.floor(Math.random() * 90000000) + 10000000;
  updateParam('seed', randomSeed.toString());
}

const exportUrl = computed(() => {
  return `/export?seed=${props.seed}&locale=${props.locale}&likes=${props.likes}&reviews=${props.reviews}&page=${props.page}`;
});
</script>

<style scoped>
.custom-range::-webkit-slider-thumb {
  background-color: #3b82f6;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  cursor: pointer;
}
.custom-range::-moz-range-thumb {
  background-color: #3b82f6;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  cursor: pointer;
}
.hover-bg-white:hover {
  background-color: #ffffff;
}
</style>
