<template>
  <div class="min-vh-100 bg-light d-flex flex-column">
    <header
      class="navbar navbar-expand-lg navbar-dark bg-dark py-3 border-bottom border-dark border-opacity-50 shadow-sm sticky-top">
      <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-white fs-4" href="/">
          <div class="p-2 bg-primary rounded-3 d-flex align-items-center justify-content-center shadow-sm">
            <iconify-icon icon="solar:videocamera-record-bold" width="24" class="text-white"></iconify-icon>
          </div>
          <span class="font-monospace">CineForge</span>
          <span
            class="badge bg-primary-subtle text-primary text-xs border border-primary-subtle rounded-pill ms-1">Movie
            Store Showcase</span>
        </a>

        <div class="d-flex align-items-center gap-3">
          <span class="badge bg-dark border text-light font-monospace d-none d-sm-inline-block">
            <iconify-icon icon="solar:shield-check-bold" class="text-success me-1"></iconify-icon>
            Deterministic Seed Engine
          </span>
        </div>
      </div>
    </header>
    <main class="container-fluid px-4 py-4 flex-grow-1">
      <Toolbar :seed="seed" :locale="locale" :likes="likes" :reviews="reviews" :view="view" :locales="locales"
        :page="page" @update:param="handleParamChange" />

      <div v-if="view === 'table'">
        <TableView :movies="currentMovies" :page="page" @change-page="handlePageChange" />
      </div>

      <div v-else>
        <GalleryView :movies="galleryMovies" :loading="loadingMore" @load-more="handleLoadMore" />
      </div>
    </main>

    <footer class="bg-white border-top py-3 text-center text-muted text-xs mt-auto">
      <div class="container-fluid px-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
        <span>&copy; 2026 CineForge Fake Movie Store Showcase. All Data Seed-Generated Server Side.</span>
        <span class="font-monospace text-xs text-muted">Symfony • Vue 3 • Inertia.js</span>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Toolbar from '../../Components/Toolbar.vue';
import TableView from '../../Components/TableView.vue';
import GalleryView from '../../Components/GalleryView.vue';

const props = defineProps({
  seed: [String, Number],
  locale: String,
  likes: Number,
  reviews: Number,
  page: Number,
  view: String,
  locales: Object,
  batch: Object,
});

const currentMovies = ref(props.batch?.movies || []);
const galleryMovies = ref([...(props.batch?.movies || [])]);
const galleryPage = ref(props.page);
const loadingMore = ref(false);

watch(() => props.batch, (newBatch) => {
  currentMovies.value = newBatch?.movies || [];
  if (props.page === 1) {
    galleryMovies.value = [...(newBatch?.movies || [])];
    galleryPage.value = 1;
  }
}, { deep: true });

function handleParamChange({ key, val }) {
  const newParams = {
    seed: props.seed,
    locale: props.locale,
    likes: props.likes,
    reviews: props.reviews,
    page: props.page,
    view: props.view,
    [key]: val,
  };

  if (key !== 'page' && key !== 'view') {
    newParams.page = 1;
  }

  router.get('/', newParams, {
    preserveState: true,
    preserveScroll: true,
  });
}

function handlePageChange(newPage) {
  router.get('/', {
    seed: props.seed,
    locale: props.locale,
    likes: props.likes,
    reviews: props.reviews,
    page: newPage,
    view: props.view,
  }, {
    preserveState: true,
  });
}

async function handleLoadMore() {
  if (loadingMore.value) return;
  loadingMore.value = true;
  const nextPage = galleryPage.value + 1;

  try {
    const res = await fetch(`/api/movies?seed=${props.seed}&locale=${props.locale}&likes=${props.likes}&reviews=${props.reviews}&page=${nextPage}`);
    const data = await res.json();
    if (data.movies && data.movies.length > 0) {
      galleryMovies.value.push(...data.movies);
      galleryPage.value = nextPage;
    }
  } catch (err) {
    console.error('Error fetching gallery movies:', err);
  } finally {
    loadingMore.value = false;
  }
}
</script>
