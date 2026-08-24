<template>
  <div class="trailer-player-box rounded-3 overflow-hidden border shadow-sm bg-black">
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

const defaultVideo = 'https://vjs.zencdn.net/v/oceans.mp4';
const defaultPoster = 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=800&auto=format&fit=crop&q=80';

const videoUrl = computed(() => props.spec?.video_url || defaultVideo);
const posterUrl = computed(() => props.spec?.poster_url || defaultPoster);
</script>

<style scoped>
.trailer-player-box {
  width: 100%;
  aspect-ratio: 16 / 9;
}
:deep(.plyr) {
  width: 100% !important;
  height: 100% !important;
  border-radius: 8px;
}
</style>
