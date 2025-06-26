<template>
    <div class="container">
        <div v-if="loading" class="text-center">Загрузка счета...</div>
        <div v-else-if="!invoice" class="alert alert-danger">Не удалось загрузить данные счета.</div>
        <div v-else class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2>Счет №{{ invoice.invoice_number }}</h2>
                        <!-- === ИЗМЕНЕНИЕ ЗДЕСЬ: СТАТУС С КНОПКАМИ === -->
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-sm dropdown-toggle" :class="statusButtonClass(invoice.status)" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ formatStatus(invoice.status) }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" @click.prevent="changeStatus('draft')">Черновик</a></li>
                                <li><a class="dropdown-item" href="#" @click.prevent="changeStatus('sent')">Отправлен</a></li>
                                <li><a class="dropdown-item" href="#" @click.prevent="changeStatus('paid')">Оплачен</a></li>
                                <li><a class="dropdown-item" href="#" @click.prevent="changeStatus('overdue')">Просрочен</a></li>
                            </ul>
                        </div>
                    </div>
                    <div>
                        <a :href="`/invoices/${invoice.id}/download`" class="btn btn-secondary">Скачать PDF</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- ... (остальная часть шаблона без изменений) ... -->
                <div class="row mb-4">
                    <div class="col-md-6"><h5>От кого:</h5><p class="mb-0">{{ invoice.user?.name || 'Исполнитель' }}</p></div>
                    <div class="col-md-6 text-md-end"><h5>Кому:</h5><p class="mb-0"><strong>{{ invoice.client?.name }}</strong></p></div>
                </div>
                <div class="row mb-4">
                     <div class="col-md-6"><h5>Дата выставления:</h5><p class="mb-0">{{ formatDate(invoice.issue_date) }}</p></div>
                    <div class="col-md-6 text-md-end"><h5>Оплатить до:</h5><p class="mb-0">{{ formatDate(invoice.due_date) }}</p></div>
                </div>
                <table class="table table-bordered">
                    <thead class="table-light"><tr><th scope="col">Описание</th><th scope="col" class="text-end">Кол-во (часы)</th><th scope="col" class="text-end">Цена за час</th><th scope="col" class="text-end">Сумма</th></tr></thead>
                    <tbody><tr v-for="item in invoice.items" :key="item.id"><td>{{ item.description }}</td><td class="text-end">{{ item.quantity }}</td><td class="text-end">{{ formatCurrency(item.unit_price) }}</td><td class="text-end">{{ formatCurrency(item.subtotal) }}</td></tr></tbody>
                    <tfoot><tr><td colspan="3" class="text-end border-0"><strong>Итого:</strong></td><td class="text-end border-0"><strong>{{ formatCurrency(invoice.total_amount) }}</strong></td></tr></tfoot>
                </table>
                <div v-if="invoice.notes" class="mt-4"><h5>Примечания:</h5><p>{{ invoice.notes }}</p></div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({ invoiceId: { type: Number, required: true } });

const invoice = ref(null);
const loading = ref(true);

const fetchInvoice = async () => { /* ... (без изменений) ... */ };

// === НОВЫЙ МЕТОД ДЛЯ СМЕНЫ СТАТУСА ===
const changeStatus = async (newStatus) => {
    if (!invoice.value) return;
    try {
        const response = await axios.patch(`/api/invoices/${props.invoiceId}/status`, {
            status: newStatus,
        });
        // Обновляем данные на странице без перезагрузки
        invoice.value = response.data;
    } catch (error) {
        console.error("Не удалось изменить статус:", error);
        alert("Произошла ошибка при смене статуса.");
    }
};

// --- Вспомогательные функции ---
const formatDate = (dateString) => new Date(dateString).toLocaleDateString('ru-RU');
const formatCurrency = (amount) => new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB' }).format(amount);
const formatStatus = (status) => { const statuses = { draft: 'Черновик', sent: 'Отправлен', paid: 'Оплачен', overdue: 'Просрочен' }; return statuses[status] || status; };


const statusButtonClass = (status) => {
    const classes = {
        draft: 'btn-secondary',
        sent: 'btn-primary',
        paid: 'btn-success',
        overdue: 'btn-danger'
    };
    return classes[status] || 'btn-light';
};

onMounted(async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/api/invoices/${props.invoiceId}`);
        invoice.value = response.data;
    } catch (error) {
        console.error("Не удалось загрузить счет:", error);
    } finally {
        loading.value = false;
    }
});
</script>