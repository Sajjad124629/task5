<template>
  <div class="trailer-player-box rounded-3 overflow-hidden border shadow-sm bg-black position-relative">
    <div class="w-100 h-100 overflow-hidden" :style="filterStyle">
      <vue-plyr>
        <video
          playsinline
          controls
          :data-poster="posterUrl"
        >
          <source :src="videoUrl" type="video/mp4" />
          Your browser does not support the video tag.
        </video>
      </vue-plyr>
    </div>

    <div class="position-absolute top-0 start-50 translate-middle-x mt-3 pointer-events-none z-2 text-center">
      <span class="badge bg-black bg-opacity-75 text-warning font-monospace px-3 py-2 border border-warning-subtle shadow-lg">
        🎬 {{ movieTitle }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import VuePlyr from 'vue-plyr';
import 'vue-plyr/dist/vue-plyr.css';

const props = defineProps({
  spec: {
    type: Object,
    default: () => ({}),
  },
});

const defaultVideo = 'https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4';
const defaultPoster = 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=800&auto=format&fit=crop&q=80';

const videoUrl = computed(() => props.spec?.video_url || defaultVideo);
const posterUrl = computed(() => props.spec?.poster_url || defaultPoster);
const movieTitle = computed(() => props.spec?.title || 'MOVIE TRAILER');

const filterStyle = computed(() => {
  const { filter_hue = 0, filter_contrast = 120, filter_saturate = 140, zoom_scale = 1.05 } = props.spec || {};
  return {
    filter: `hue-rotate(${filter_hue}deg) contrast(${filter_contrast}%) saturate(${filter_saturate}%)`,
    transform: `scale(${zoom_scale})`,
    width: '100%',
    height: '100%',
  };
});
</script>

<style scoped>
.trailer-player-box {
  width: 100%;
  aspect-ratio: 16 / 9;
}
.pointer-events-none {
  pointer-events: none;
}
:deep(.plyr) {
  width: 100% !important;
  height: 100% !important;
  border-radius: 8px;
}
</style>
