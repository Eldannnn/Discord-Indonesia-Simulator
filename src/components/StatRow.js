export default function StatRow() {
  const stats = [
    { label: "PLAYERS", value: "21" },
    { label: "ACTIVE ELECTIONS", value: "486" },
    { label: "BILLS IN MOTION", value: "3" },
    { label: "LAWS ENACTED", value: "11" },
    { label: "PARTY ORGS", value: "2" },
  ];

  return (
    <div className="stats-container">
      {stats.map((stat, index) => (
        <div key={index} className="card stat-box">
          <span className="stat-label">{stat.label}</span>
          <span className="stat-value">{stat.value}</span>
        </div>
      ))}
    </div>
  );
}
