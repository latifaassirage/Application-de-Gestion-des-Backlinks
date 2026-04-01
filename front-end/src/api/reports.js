import api from './api';

export const getAllReports = () => api.get('/reports');
export const getReportById = (id) => api.get(`/reports/${id}`);
export const createReport = (data) => api.post('/reports', data);
export const updateReport = (id, data) => api.put(`/reports/${id}`, data);
export const deleteReport = (id) => api.delete(`/reports/${id}`);

// Fonction dédiée pour l'export PDF avec la bonne configuration
export const exportSummaryPdf = async () => {
  try {
    const response = await api.post('/reports/summary-pdf', {}, {
      responseType: 'blob',
      headers: {
        'Accept': 'application/pdf'
      }
    });
    return response;
  } catch (error) {
    throw error;
  }
};
