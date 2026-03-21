// NOUVELLE FONCTION exportToExcel À AJOUTER DANS Reports.jsx
// Remplacez complètement l'ancienne fonction exportExcel par celle-ci

const exportToExcel = () => {
  try {
    setExporting(prev => ({ ...prev, excel: true }));
    
    // Créer les en-têtes dynamiques basées sur selectedColumns
    const headers = Object.keys(selectedColumns)
      .filter(col => selectedColumns[col])
      .map(col => columnLabels[col]);

    // Créer les données de tableau dynamiques
    const data = reportData.backlinks.map(backlink => {
      const row = {};
      Object.keys(selectedColumns)
        .filter(col => selectedColumns[col])
        .forEach(col => {
          // Mapper les clés de colonnes aux valeurs réelles des backlinks
          switch(col) {
            case 'date_added':
              row[columnLabels[col]] = new Date(backlink.date_added).toLocaleDateString();
              break;
            case 'source_website':
              row[columnLabels[col]] = getSourceDomain(backlink.source_site_id);
              break;
            case 'traffic':
              row[columnLabels[col]] = backlink.source_site?.traffic_estimated || backlink.traffic_estimated || 'N/A';
              break;
            case 'type':
              row[columnLabels[col]] = backlink.type || '-';
              break;
            case 'target_url':
              row[columnLabels[col]] = backlink.target_url || '-';
              break;
            case 'anchor_text':
              row[columnLabels[col]] = backlink.anchor_text || '-';
              break;
            case 'placement_url':
              row[columnLabels[col]] = backlink.placement_url || '-';
              break;
            case 'status':
              row[columnLabels[col]] = backlink.status || '-';
              break;
            case 'quality_score':
              row[columnLabels[col]] = `${backlink.dynamic_quality_score || 3}/5`;
              break;
            case 'cost':
              row[columnLabels[col]] = backlink.cost || 0;
              break;
            default:
              row[columnLabels[col]] = '-';
          }
        });
      return row;
    });

    // Créer le workbook et la worksheet
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.json_to_sheet(data, { header: headers });
    
    // Ajuster la largeur des colonnes
    const colWidths = headers.map((header, index) => {
      // Définir la largeur basée sur le type de contenu
      const maxWidth = Math.max(
        header.length,
        ...data.map(row => (Object.values(row)[index] || '').toString().length)
      );
      return { wch: Math.min(maxWidth + 2, 30) }; // Max 30 characters width
    });
    ws['!cols'] = colWidths;

    // Ajouter la worksheet au workbook
    XLSX.utils.book_append_sheet(wb, ws, 'Backlinks Report');
    
    // Ajouter une worksheet pour le résumé
    const summaryData = [
      ['Statistiques', 'Valeur'],
      ['Total Backlinks', reportData.summary.total],
      ['Live', reportData.summary.live],
      ['Lost', reportData.summary.lost],
      ['Pending', reportData.summary.pending],
      ['Paid', reportData.summary.paid],
      ['Free', reportData.summary.free],
      ['Coût Total', reportData.summary.totalCost]
    ];
    
    const summaryWs = XLSX.utils.aoa_to_sheet(summaryData);
    XLSX.utils.book_append_sheet(wb, summaryWs, 'Résumé');
    
    // Télécharger le fichier Excel
    const client = filters.client_id 
      ? clients.find(c => c.id === parseInt(filters.client_id))
      : null;
    const clientName = client ? client.company_name : 'tous-les-clients';
    
    const fileName = `rapport-backlinks-${clientName.replace(/\s+/g, '-').toLowerCase()}-${new Date().toISOString().split('T')[0]}.xlsx`;
    
    XLSX.writeFile(wb, fileName);
    
  } catch (error) {
    console.error("Error generating Excel:", error);
    alert("Erreur lors de la génération du fichier Excel");
  } finally {
    setExporting(prev => ({ ...prev, excel: false }));
  }
};

// INSTRUCTIONS :
// 1. Remplacez complètement l'ancienne fonction exportPDF par la nouvelle
// 2. Remplacez complètement l'ancienne fonction exportExcel par celle-ci
// 3. Assurez-vous que les bibliothèques jsPDF, jspdf-autotable et xlsx sont importées
// 4. Supprimez les appels API backend pour les exports (tout se fait maintenant localement)
