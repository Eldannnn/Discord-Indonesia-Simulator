'use client';

export default function ActionPanel() {
  const handleAction = (actionType) => {
    alert(`Tindakan: ${actionType} diproses.`);
  };

  return (
    <div className="glass-panel animate-fade-in delay-3" style={{ flex: 1 }}>
      <h3 style={{ marginBottom: '1rem' }}>Tindakan Eksekutif</h3>
      <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
        <button className="btn-primary" onClick={() => handleAction('Terbitkan Perppu')}>
          Terbitkan Perppu
        </button>
        <button className="btn-primary" style={{ background: 'var(--surface-light)', color: 'var(--foreground)' }} onClick={() => handleAction('Ajukan RUU Baru')}>
          Ajukan RUU Baru
        </button>
        <button className="btn-primary" style={{ background: 'var(--surface-light)', color: 'var(--foreground)' }} onClick={() => handleAction('Reshuffle Kabinet')}>
          Reshuffle Kabinet
        </button>
      </div>
    </div>
  );
}
