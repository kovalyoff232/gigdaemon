<template>
    <div v-if="!loading" class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">К оплате</h5>
                    <p class="card-text fs-4">{{ formatCurrency(summaryData.totalUnpaid) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Доход (мес.)</h5>
                    <p class="card-text fs-4">{{ formatCurrency(summaryData.incomeThisMonth) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Неоплаченные часы</h5>
                    <p class="card-text fs-4">{{ summaryData.unbilledHours }} ч.</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100" :class="{ 'bg-primary text-white': summaryData.activeTimer }">
                <div class="card-body">
                     <h5 class="card-title" :class="{'text-white-50': summaryData.activeTimer}">Активный таймер</h5>
                    <div v-if="summaryData.activeTimer" class="fs-5">
                       <span class="text-white stretched-link text-decoration-none">
                           {{ summaryData.activeTimer.project.title }}
                       </span>
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
const props = defineProps({
    summaryData: {
        type: Object,
        required: true,
    },
    loading: {
        type: Boolean,
        required: true,
    }
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', minimumFractionDigits: 0 }).format(amount);
};
</script>