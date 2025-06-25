<template>
    <div v-if="!loading" class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">К оплате</h5>
                    <p class="card-text fs-4">{{ formatCurrency(summary.totalUnpaid) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">Доход (мес.)</h5>
                    <p class="card-text fs-4">{{ formatCurrency(summary.incomeThisMonth) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">Неоплаченные часы</h5>
                    <p class="card-text fs-4">{{ summary.unbilledHours }} ч.</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center" :class="{ 'bg-primary text-white': summary.activeTimer }">
                <div class="card-body">
                     <h5 class="card-title" :class="{'text-white-50': summary.activeTimer}">Активный таймер</h5>
                    <div v-if="summary.activeTimer" class="fs-5">
                       <a :href="`/invoices`" class="text-white stretched-link text-decoration-none">
                           {{ summary.activeTimer.project.title }}
                       </a>
                    </div>
                     <p v-else class="card-text fs-4 text-muted">Выключен</p>
                </div>
            </div>
        </div>
    </div>
    <div v-else class="text-center mb-4">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(true);
const summary = reactive({
    totalUnpaid: 0,
    incomeThisMonth: 0,
    unbilledHours: 0,
    activeTimer: null,
});

const fetchSummary = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/dashboard-summary');
        Object.assign(summary, response.data);
    } catch (error) {
        console.error("Не удалось загрузить сводку:", error);
    } finally {
        loading.value = false;
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', minimumFractionDigits: 0 }).format(amount);
};

onMounted(() => {
    fetchSummary();
});
</script>