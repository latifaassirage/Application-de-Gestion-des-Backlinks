// NOUVELLE FONCTION exportToPDF À AJOUTER DANS Reports.jsx
// Remplacez complètement l'ancienne fonction exportPDF par celle-ci

const exportToPDF = () => {
  try {
    setExporting(prev => ({ ...prev, pdf: true }));
    
    // Créer les en-têtes dynamiques basées sur selectedColumns
    const headers = Object.keys(selectedColumns)
      .filter(col => selectedColumns[col])
      .map(col => columnLabels[col]);

    // Créer les données de tableau dynamiques
    const body = reportData.backlinks.map(backlink => 
      Object.keys(selectedColumns)
        .filter(col => selectedColumns[col])
        .map(col => {
          // Mapper les clés de colonnes aux valeurs réelles des backlinks
          switch(col) {
            case 'date_added':
              return new Date(backlink.date_added).toLocaleDateString();
            case 'source_website':
              return getSourceDomain(backlink.source_site_id);
            case 'traffic':
              return backlink.source_site?.traffic_estimated || backlink.traffic_estimated || 'N/A';
            case 'type':
              return backlink.type || '-';
            case 'target_url':
              return backlink.target_url || '-';
            case 'anchor_text':
              return backlink.anchor_text || '-';
            case 'placement_url':
              return backlink.placement_url || '-';
            case 'status':
              return backlink.status || '-';
            case 'quality_score':
              return `${backlink.dynamic_quality_score || 3}/5`;
            case 'cost':
              return `$${backlink.cost || '0'}`;
            default:
              return '-';
          }
        })
    );

    // Initialiser jsPDF
    const doc = new jsPDF();
    
    // Ajouter le titre
    doc.setFontSize(20);
    doc.text('RAPPORT DE BACKLINKS', 105, 20, { align: 'center' });
    
    // Ajouter la date de génération
    doc.setFontSize(12);
    doc.text(`Généré le : ${new Date().toLocaleDateString('fr-FR')}`, 105, 30, { align: 'center' });
    
    // Ajouter la période si applicable
    if (filters.start_date && filters.end_date) {
      doc.text(`Période : Du ${filters.start_date} au ${filters.end_date}`, 105, 40, { align: 'center' });
    } else {
      doc.text('Période : Rapport Global', 105, 40, { align: 'center' });
    }
    
    // Ajouter les statistiques
    doc.setFontSize(14);
    doc.text('Résumé', 20, 60);
    doc.setFontSize(10);
    
    const summary = reportData.summary;
    const summaryY = 70;
    doc.text(`Total: ${summary.total}`, 20, summaryY);
    doc.text(`Live: ${summary.live}`, 60, summaryY);
    doc.text(`Lost: ${summary.lost}`, 100, summaryY);
    doc.text(`Paid: ${summary.paid}`, 140, summaryY);
    doc.text(`Coût Total: $${summary.totalCost.toFixed(2)}`, 20, summaryY + 10);
    
    // Générer le tableau avec autoTable
    doc.autoTable({
      head: [headers],
      body: body,
      startY: summaryY + 25,
      theme: 'grid',
      styles: {
        fontSize: 9,
        cellPadding: 3,
      },
      headStyles: {
        fillColor: [44, 62, 80],
        textColor: 255,
        fontStyle: 'bold',
      },
      alternateRowStyles: {
        fillColor: [245, 245, 245],
      },
      columnStyles: {
        0: { cellWidth: 20 }, // Date
        1: { cellWidth: 25 }, // Source
        2: { cellWidth: 15 }, // Traffic/Type/Score
        3: { cellWidth: 30 }, // Target URL/Placement URL
        4: { cellWidth: 20 }, // Anchor Text
        5: { cellWidth: 15 }, // Status
        6: { cellWidth: 15 }, // Cost
      },
      didDrawPage: (data) => {
        // Footer
        doc.setFontSize(9);
        doc.setTextColor(150);
        doc.text(
          'Gestion Backlinks - Rapport Confidentiel - Page ' + doc.internal.getNumberOfPages(),
          105,
          doc.internal.pageSize.height - 10,
          { align: 'center' }
        );
      },
    });
    
    // Télécharger le PDF
    const client = filters.client_id 
      ? clients.find(c => c.id === parseInt(filters.client_id))
      : null;
    const clientName = client ? client.company_name : 'tous-les-clients';
    
    const fileName = `rapport-backlinks-${clientName.replace(/\s+/g, '-').toLowerCase()}-${new Date().toISOString().split('T')[0]}.pdf`;
    
    doc.save(fileName);
    
  } catch (error) {
    console.error("Error generating PDF:", error);
    alert("Erreur lors de la génération du PDF");
  } finally {
    setExporting(prev => ({ ...prev, pdf: false }));
  }
};

// SUPPRIMEZ l'ancienne fonction exportPDF qui utilisait l'API backend
// GARDER la fonction exportExcel existante (elle fonctionne déjà avec le backend)
