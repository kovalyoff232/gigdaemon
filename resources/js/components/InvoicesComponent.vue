<template>
    <div class="container">
        <!-- Заголовок и кнопка -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Счета</h1>
            <button class="btn btn-primary" @click="openCreateInvoiceModal">Выставить новый счет</button>
        </div>

        <!-- Список счетов -->
        <div v-if="loading" class="text-center">Загрузка счетов...</div>
        <div v-else-if="invoices.length === 0" class="card">
            <div class="card-body text-center">
                У вас пока нет ни одного счета.
            </div>
        </div>
        <div v-else class="card">
            <div class="list-group list-group-flush">
                <a v-for="invoice in invoices" :key="invoice.id" :href="`/invoices/${invoice.id}`" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-primary">Счет №{{ invoice.invoice_number }}</h5>
                        <p class="mb-1">Клиент: <strong>{{ invoice.client.name }}</strong></p>
                        <small>Дата выставления: {{ formatDate(invoice.issue_date) }}</small>
                    </div>
                    <div class="text-end">
                        <span class="fs-5 me-3">{{ formatCurrency(invoice.total_amount, invoice.currency) }}</span>
                        <span :class="statusBadgeClass(invoice.status)" class="badge">{{ formatStatus(invoice.status) }}</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- Модальное окно создания счета -->
        <div class="modal fade" id="invoiceModal" tabindex="-1" ref="invoiceModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form @submit.prevent="handleCreateInvoice">
                        <div class="modal-header">
                            <h5 class="modal-title">Новый счет</h5>
                            <button type="button" class="btn-close" @click="closeCreateInvoiceModal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Шаг 1: Выбор клиента -->
                            <div class="mb-3">
                                <label class="form-label">1. Выберите клиента</label>
                                <select class="form-select" v-model="form.client_id" @change="loadUnbilledEntries" required>
                                    <option disabled value="">Выберите...</option>
                                    <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                                </select>
                            </div>

                            <!-- Шаг 2: Выбор записей и валюты -->
                            <div v-if="form.client_id" class="mb-3">
                                <label class="form-label">2. Выберите неоплаченные записи и настройте счет</label>
                                <div v-if="unbilled.loading" class="text-center">Загрузка записей...</div>
                                <div v-else-if="unbilled.entries.length === 0" class="alert alert-light">У этого клиента нет неоплаченных часов.</div>
                                <div v-else>
                                    <div class="row gx-2 mb-2">
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <span class="input-group-text">Базовая ставка (RUB/час)</span>
                                                <input type="number" class="form-control" v-model.number="form.base_rate" step="0.01" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <select class="form-select" v-model="form.currency">
                                                <option value="RUB">RUB</option>
                                                <option value="USD">USD</option>
                                                <option value="EUR">EUR</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="list-group" style="max-height: 200px; overflow-y: auto;">
                                        <label v-for="entry in unbilled.entries" :key="entry.id" class="list-group-item d-flex justify-content-between">
                                            <div>
                                                <input class="form-check-input me-2" type="checkbox" :value="entry.id" v-model="form.time_entry_ids">
                                                {{ entry.description || 'Без описания' }}
                                                <small class="text-muted d-block">{{ entry.project?.title }} ({{ formatDate(entry.start_time) }})</small>
                                            </div>
                                            <span class="badge bg-secondary rounded-pill">{{ formatDuration(entry.duration) }}</span>
                                        </label>
                                    </div>
                                    <div class="form-text">Итого выбрано: {{ formatDuration(totalSecondsSelected) }}</div>
                                </div>
                            </div>

                            <!-- Шаг 3: Даты -->
                            <div v-if="form.time_entry_ids.length > 0" class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small">Дата выставления</label>
                                    <input type="date" class="form-control" v-model="form.issue_date" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small">Оплатить до</label>
                                    <input type="date" class="form-control" v-model="form.due_date" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closeCreateInvoiceModal">Отмена</button>
                            <button type="submit" class="btn btn-primary" :disabled="form.time_entry_ids.length === 0 || creating">
                                <span v-if="creating" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Выставить счет
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';
import { useToast } from 'vue-toastification';

const toast = useToast();

// --- Состояния компонента ---
const invoices = ref([]);
const clients = ref([]);
const loading = ref(true);
const creating = ref(false);
let invoiceModalInstance = null;
const invoiceModal = ref(null);
const unbilled = reactive({ loading: false, entries: [] });


const form = reactive({
    client_id: '',
    time_entry_ids: [],
    base_rate: 1000, // Ставка в основной валюте
    currency: 'RUB', // Валюта счета
    issue_date: new Date().toISOString().slice(0, 10),
    due_date: new Date(new Date().setDate(new Date().getDate() + 14)).toISOString().slice(0, 10),
    notes: null
});

// --- Основные методы ---
const fetchInvoices = async () => {
    try {
        const response = await axios.get('/api/invoices');
        invoices.value = response.data;
    } catch (error) {
        toast.error("Произошла ошибка при загрузке счетов.");
        console.error("Не удалось загрузить счета:", error);
    }
};

const fetchClients = async () => {
    try {
        const response = await axios.get('/api/clients');
        clients.value = response.data;
    } catch (e) {
        toast.error("Ошибка загрузки клиентов.");
        console.error("Ошибка загрузки клиентов:", e)
    }
};

const loadUnbilledEntries = async () => {
    if (!form.client_id) return;
    const selectedClient = clients.value.find(c => c.id === form.client_id);
    
    form.base_rate = selectedClient?.default_rate || 1000;
    form.currency = selectedClient?.default_currency || 'RUB';

    unbilled.loading = true;
    form.time_entry_ids = [];
    try {
        const response = await axios.get(`/api/clients/${form.client_id}/unbilled-entries`);
        unbilled.entries = response.data;
    } catch (error) {
        toast.error("Не удалось загрузить неоплаченные записи.");
        console.error(error);
    } finally {
        unbilled.loading = false;
    }
};

const handleCreateInvoice = async () => {
    creating.value = true;
    try {
        // Переименовываем unit_price в base_rate для отправки на бэкенд
        const payload = { ...form, base_rate: form.base_rate };
        const response = await axios.post('/api/invoices', payload);
        invoices.value.unshift(response.data);
        toast.success(`Счет №${response.data.invoice_number} успешно создан!`);
        closeCreateInvoiceModal();
    } catch (error) {
        if (error.response?.status === 422) {
            const errors = Object.values(error.response.data.errors).flat();
            toast.error(`Ошибка валидации: ${errors.join(' ')}`);
        } else if (error.response?.data?.message) {
            toast.error(`Ошибка: ${error.response.data.message}`);
        } else {
            toast.error("Не удалось создать счет.");
        }
        console.error(error);
    } finally {
        creating.value = false;
    }
};

// --- Методы для модального окна ---
const openCreateInvoiceModal = () => {
    Object.assign(form, {
        client_id: '',
        time_entry_ids: [],
        base_rate: 1000,
        currency: 'RUB',
        issue_date: new Date().toISOString().slice(0, 10),
        due_date: new Date(new Date().setDate(new Date().getDate() + 14)).toISOString().slice(0, 10),
        notes: null
    });
    unbilled.entries = [];
    invoiceModalInstance?.show();
};

const closeCreateInvoiceModal = () => {
    invoiceModalInstance?.hide();
};

// --- Вспомогательные методы и вычисляемые свойства ---
const totalSecondsSelected = computed(() => {
    if (unbilled.entries.length === 0) return 0;
    return form.time_entry_ids.reduce((total, id) => {
        const entry = unbilled.entries.find(e => e.id === id);
        return total + (entry ? entry.duration : 0);
    }, 0);
});

const formatCurrency = (amount, currencyCode = 'RUB') => {
    return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: currencyCode }).format(amount);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ru-RU');
};

const formatDuration = (totalSeconds) => {
    if (totalSeconds === null) return '0ч 0м';
    const h = Math.floor(totalSeconds / 3600);
    const m = Math.floor((totalSeconds % 3600) / 60);
    return `${h}ч ${m}м`;
};

const formatStatus = (status) => {
    const statuses = { draft: 'Черновик', sent: 'Отправлен', paid: 'Оплачен', overdue: 'Просрочен' };
    return statuses[status] || status;
};

const statusBadgeClass = (status) => {
    const classes = { draft: 'bg-secondary', sent: 'bg-primary', paid: 'bg-success', overdue: 'bg-danger' };
    return classes[status] || 'bg-light text-dark';
};

// --- Хук жизненного цикла ---
onMounted(async () => {
    loading.value = true;
    invoiceModalInstance = invoiceModal.value ? new Modal(invoiceModal.value) : null;
    await Promise.all([
        fetchInvoices(),
        fetchClients()
    ]);
    loading.value = false;
});
</script>