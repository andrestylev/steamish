import { Bar, Pie, Line } from 'react-chartjs-2';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    PointElement,
    LineElement,
    Filler,
} from 'chart.js';

// Register Chart.js components once (idempotent)
ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    PointElement,
    LineElement,
    Filler,
);

/**
 * Reusable Chart.js wrapper widget.
 *
 * @param {('bar'|'pie'|'line')} type - Chart type
 * @param {object} data - Chart.js data object ({labels, datasets})
 * @param {object} [options] - Additional Chart.js options (merged with defaults)
 * @param {string} [title] - Chart heading
 * @param {boolean} [isEmpty] - When true, renders empty-state message instead
 * @param {string} [emptyMessage] - Custom empty-state message
 */
export default function ChartWidget({ type = 'bar', data, options, title, isEmpty, emptyMessage }) {
    if (isEmpty || !data || !data.labels || data.labels.length === 0) {
        return (
            <div className="d-flex align-items-center justify-content-center" style={{ height: 250 }}>
                <p className="text-secondary mb-0 small">{emptyMessage || 'No data available.'}</p>
            </div>
        );
    }

    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    color: '#8f98a0',
                    boxWidth: 12,
                    padding: 16,
                    font: { size: 11 },
                },
            },
            tooltip: {
                backgroundColor: '#1b2838',
                titleColor: '#ffffff',
                bodyColor: '#acb2b8',
                borderColor: '#2a475e',
                borderWidth: 1,
                padding: 10,
                cornerRadius: 4,
            },
        },
        scales: type !== 'pie'
            ? {
                x: {
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: { color: '#8f98a0', font: { size: 10 } },
                },
                y: {
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: { color: '#8f98a0', font: { size: 10 } },
                    beginAtZero: true,
                },
            }
            : undefined,
    };

    const mergedOptions = {
        ...defaultOptions,
        ...options,
        plugins: {
            ...defaultOptions.plugins,
            ...(options?.plugins || {}),
            legend: {
                ...defaultOptions.plugins.legend,
                ...(options?.plugins?.legend || {}),
            },
            tooltip: {
                ...defaultOptions.plugins.tooltip,
                ...(options?.plugins?.tooltip || {}),
            },
        },
        scales: type !== 'pie'
            ? {
                ...defaultOptions.scales,
                ...(options?.scales || {}),
                x: { ...(defaultOptions.scales?.x || {}), ...(options?.scales?.x || {}) },
                y: { ...(defaultOptions.scales?.y || {}), ...(options?.scales?.y || {}) },
            }
            : undefined,
    };

    const ChartComponent = type === 'pie' ? Pie : type === 'line' ? Line : Bar;

    return (
        <div>
            {title && (
                <h6 className="fw-bold mb-3 text-white">{title}</h6>
            )}
            <div style={{ height: 250, position: 'relative' }}>
                <ChartComponent data={data} options={mergedOptions} />
            </div>
        </div>
    );
}
