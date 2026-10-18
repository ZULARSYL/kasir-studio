@extends('template.index')

@section('title', 'Dashboard Admin | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">
    <!-- ----------------------------------------------------
         1. HERO WELCOME BANNER (MATCHING LAYOUT.PNG)
    ---------------------------------------------------- -->
    <div class="welcome-hero-card mb-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-3">
                    <div class="hero-icon-box">
                        <i class="bi bi-house-door-fill"></i>
                    </div>
                    <div>
                        <h2 class="hero-title mb-1">Welcome back, {{ auth()->user()->name ?? 'Administrator' }}!</h2>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <button type="button" class="btn btn-hero-pill">
                        <i class="bi bi-calendar3 me-1"></i> Month to Date
                    </button>
                    <button type="button" class="btn btn-hero-pill">
                        <i class="bi bi-activity me-1"></i> Year to Date
                    </button>
                    <button type="button" class="btn btn-hero-pill btn-hero-solid">
                        <i class="bi bi-download me-1"></i> Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ----------------------------------------------------
         2. FOUR METRIC KPI CARDS (MATCHING LAYOUT.PNG)
    ---------------------------------------------------- -->
    <div class="row g-3 g-xl-4 mb-4">
        <!-- 1. Total Sales -->
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="metric-icon-box bg-emerald-light text-emerald">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <span class="badge bg-emerald-subtle text-emerald rounded-pill px-2 py-1 fw-semibold font-sm">
                        <i class="bi bi-arrow-up-short"></i> 8.2%
                    </span>
                </div>
                <div class="metric-value">786</div>
                <div class="metric-label mb-3">TOTAL SALES</div>
                <div class="metric-progress-wrapper">
                    <div class="metric-progress-bar bg-emerald" style="width: 72%;"></div>
                </div>
            </div>
        </div>

        <!-- 2. Revenue -->
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="metric-icon-box bg-blue-light text-blue">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <span class="badge bg-emerald-subtle text-emerald rounded-pill px-2 py-1 fw-semibold font-sm">
                        <i class="bi bi-arrow-up-short"></i> 12.5%
                    </span>
                </div>
                <div class="metric-value">Rp 17.584.000</div>
                <div class="metric-label mb-3">REVENUE</div>
                <div class="metric-progress-wrapper">
                    <div class="metric-progress-bar bg-blue" style="width: 60%;"></div>
                </div>
            </div>
        </div>

        <!-- 3. Cost / Pengeluaran -->
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="metric-icon-box bg-rose-light text-rose">
                        <i class="bi bi-arrow-down-right"></i>
                    </div>
                    <span class="badge bg-rose-subtle text-rose rounded-pill px-2 py-1 fw-semibold font-sm">
                        <i class="bi bi-arrow-down-short"></i> 3.1%
                    </span>
                </div>
                <div class="metric-value">Rp 12.487.000</div>
                <div class="metric-label mb-3">COST</div>
                <div class="metric-progress-wrapper">
                    <div class="metric-progress-bar bg-rose" style="width: 48%;"></div>
                </div>
            </div>
        </div>

        <!-- 4. Profit -->
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="metric-icon-box bg-amber-light text-amber">
                        <i class="bi bi-bar-chart-fill"></i>
                    </div>
                    <span class="badge bg-emerald-subtle text-emerald rounded-pill px-2 py-1 fw-semibold font-sm">
                        <i class="bi bi-arrow-up-short"></i> 18.4%
                    </span>
                </div>
                <div class="metric-value">Rp 5.097.000</div>
                <div class="metric-label mb-3">PROFIT</div>
                <div class="metric-progress-wrapper">
                    <div class="metric-progress-bar bg-amber" style="width: 38%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ----------------------------------------------------
         3. SALES STATISTICS & REVENUE BREAKDOWN
    ---------------------------------------------------- -->
    <div class="row g-3 g-xl-4">
        <!-- Sales Statistics Area Chart -->
        <div class="col-xl-8">
            <div class="content-panel-card h-100">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="metric-icon-box bg-emerald-light text-emerald">
                            <i class="bi bi-activity"></i>
                        </div>
                        <div>
                            <h5 class="panel-card-title mb-0">Sales Statistics</h5>
                            <span class="text-muted small">Monthly revenue overview</span>
                        </div>
                    </div>
                    <div class="btn-group panel-period-pill" role="group">
                        <button type="button" class="btn btn-period">Daily</button>
                        <button type="button" class="btn btn-period">Weekly</button>
                        <button type="button" class="btn btn-period active">Monthly</button>
                    </div>
                </div>

                <div class="d-flex align-items-baseline gap-3 mb-3">
                    <div class="panel-stat-number">Rp 67.347.000</div>
                    <span class="badge bg-emerald-subtle text-emerald rounded-pill px-2 py-1 fw-semibold">
                        <i class="bi bi-arrow-up-short"></i> 8.2% vs last period
                    </span>
                </div>

                <!-- Custom SVG Area Line Chart Matching Screenshot -->
                <div class="chart-container py-2">
                    <svg viewBox="0 0 700 240" class="w-100" style="overflow: visible;">
                        <defs>
                            <linearGradient id="emeraldGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.32"/>
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>
                        <!-- Grid Lines -->
                        <line x1="0" y1="40" x2="700" y2="40" stroke="var(--border-color)" stroke-dasharray="4,4" />
                        <line x1="0" y1="100" x2="700" y2="100" stroke="var(--border-color)" stroke-dasharray="4,4" />
                        <line x1="0" y1="160" x2="700" y2="160" stroke="var(--border-color)" stroke-dasharray="4,4" />
                        <line x1="0" y1="220" x2="700" y2="220" stroke="var(--border-color)" />

                        <!-- Area Fill -->
                        <path d="M 0,220 L 0,200 C 90,190 120,220 200,180 C 270,140 330,165 400,110 C 470,60 520,70 580,30 C 640,-5 680,45 700,50 L 700,220 Z" 
                              fill="url(#emeraldGradient)" />

                        <!-- Smooth Wave Line -->
                        <path d="M 0,200 C 90,190 120,220 200,180 C 270,140 330,165 400,110 C 470,60 520,70 580,30 C 640,-5 680,45 700,50" 
                              fill="none" 
                              stroke="#10b981" 
                              stroke-width="3.5" 
                              stroke-linecap="round" />

                        <!-- Highlight Points -->
                        <circle cx="200" cy="180" r="5" fill="#ffffff" stroke="#10b981" stroke-width="3" />
                        <circle cx="400" cy="110" r="5" fill="#ffffff" stroke="#10b981" stroke-width="3" />
                        <circle cx="580" cy="30" r="7" fill="#ffffff" stroke="#059669" stroke-width="4" />
                    </svg>
                    <div class="d-flex justify-content-between text-muted small mt-2 px-1">
                        <span>Jan</span>
                        <span>Feb</span>
                        <span>Mar</span>
                        <span>Apr</span>
                        <span>May</span>
                        <span>Jun</span>
                        <span>Jul</span>
                        <span>Aug</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Breakdown Card -->
        <div class="col-xl-4">
            <div class="content-panel-card h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="metric-icon-box bg-emerald-light text-emerald">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <h5 class="panel-card-title mb-0">Revenue Breakdown</h5>
                    </div>

                    <div class="breakdown-list">
                        <!-- Total Sales Item -->
                        <div class="breakdown-item d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <span class="breakdown-dot bg-emerald"></span>
                                <div>
                                    <div class="fw-bold text-heading">Total Sales</div>
                                    <div class="text-muted small">786 transactions</div>
                                </div>
                            </div>
                            <div class="breakdown-amount fw-bold text-heading">Rp 17.584.000</div>
                        </div>

                        <!-- Revenue Item -->
                        <div class="breakdown-item d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <span class="breakdown-dot bg-blue"></span>
                                <div>
                                    <div class="fw-bold text-heading">Revenue</div>
                                    <div class="text-muted small">Net income</div>
                                </div>
                            </div>
                            <div class="breakdown-amount fw-bold text-heading">Rp 17.584.000</div>
                        </div>

                        <!-- Cost Item -->
                        <div class="breakdown-item d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <span class="breakdown-dot bg-rose"></span>
                                <div>
                                    <div class="fw-bold text-heading">Cost</div>
                                    <div class="text-muted small">Operational & cafe stock</div>
                                </div>
                            </div>
                            <div class="breakdown-amount fw-bold text-heading">Rp 12.487.000</div>
                        </div>

                        <!-- Profit Item -->
                        <div class="breakdown-item d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="breakdown-dot bg-amber"></span>
                                <div>
                                    <div class="fw-bold text-heading">Net Profit</div>
                                    <div class="text-muted small">After expenses</div>
                                </div>
                            </div>
                            <div class="breakdown-amount fw-bold text-emerald">Rp 5.097.000</div>
                        </div>
                    </div>
                </div>

                <!-- Studio & Cafe Quick Actions -->
                <div class="pt-3 border-top mt-3">
                    <a href="{{ route('kasir.index') }}" class="btn btn-open-store w-100 py-2 rounded-3 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-shop"></i>
                        <span>Buka Sistem Kasir POS</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Welcome Green Card (Matching Invenza Hero) */
    .welcome-hero-card {
background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-dark) 100%);        border-radius: 20px;
        padding: 28px 32px;
        color: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(0, 123, 139, 0.35);
        position: relative;
        overflow: hidden;
    }

    .welcome-hero-card::after {
        content: '';
        position: absolute;
        right: -40px;
        bottom: -40px;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .hero-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #ffffff;
        flex-shrink: 0;
    }

    .hero-title {
        font-weight: 800;
        letter-spacing: -0.5px;
        font-size: 1.6rem;
    }

    .hero-subtitle {
        font-size: 0.92rem;
        opacity: 0.92;
    }

    .btn-hero-pill {
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.28);
        color: #ffffff;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 8px 16px;
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    .btn-hero-pill:hover {
        background: rgba(255, 255, 255, 0.28);
        color: #ffffff;
    }

    .btn-hero-solid {
        background: #ffffff !important;
        color: #059669 !important;
        border-color: #ffffff !important;
    }
    .btn-hero-solid:hover {
        background: #f1f5f9 !important;
        color: #047857 !important;
    }

    /* Metric Cards */
    .metric-card {
        background: var(--bg-card);
        border-radius: var(--card-radius);
        border: 1px solid var(--border-color);
        padding: 22px 24px;
        box-shadow: var(--card-shadow);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.08);
    }

    .metric-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .metric-value {
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--text-heading);
        line-height: 1.2;
    }

    .metric-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        color: var(--text-muted);
    }

    .metric-progress-wrapper {
        height: 4px;
        width: 100%;
        background-color: rgba(148, 163, 184, 0.15);
        border-radius: 9999px;
        overflow: hidden;
    }

    .metric-progress-bar {
        height: 100%;
        border-radius: 9999px;
    }

    /* Color Utilities */
    .bg-emerald { background-color: #10b981 !important; }
    .text-emerald { color: #10b981 !important; }
    .bg-emerald-light { background-color: rgba(16, 185, 129, 0.12) !important; }
    .bg-emerald-subtle { background-color: rgba(16, 185, 129, 0.14) !important; }

    .bg-blue { background-color: #3b82f6 !important; }
    .text-blue { color: #3b82f6 !important; }
    .bg-blue-light { background-color: rgba(59, 130, 246, 0.12) !important; }

    .bg-rose { background-color: #f43f5e !important; }
    .text-rose { color: #f43f5e !important; }
    .bg-rose-light { background-color: rgba(244, 63, 94, 0.12) !important; }
    .bg-rose-subtle { background-color: rgba(244, 63, 94, 0.14) !important; }

    .bg-amber { background-color: #f59e0b !important; }
    .text-amber { color: #f59e0b !important; }
    .bg-amber-light { background-color: rgba(245, 158, 11, 0.12) !important; }

    .text-heading { color: var(--text-heading) !important; }

    /* Content Panels */
    .content-panel-card {
        background: var(--bg-card);
        border-radius: var(--card-radius);
        border: 1px solid var(--border-color);
        padding: 24px 28px;
        box-shadow: var(--card-shadow);
    }

    .panel-card-title {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--text-heading);
    }

    .panel-period-pill {
        background: rgba(148, 163, 184, 0.1);
        border-radius: 10px;
        padding: 3px;
    }

    .btn-period {
        font-size: 0.78rem;
        font-weight: 600;
        padding: 4px 12px;
        border: none;
        color: var(--text-muted);
        border-radius: 8px !important;
        transition: all 0.2s ease;
    }

    .btn-period.active {
        background: #059669;
        color: #ffffff;
    }

    .panel-stat-number {
        font-size: 1.85rem;
        font-weight: 800;
        color: var(--text-heading);
    }

    .breakdown-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .btn-open-store {
        background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-dark) 100%);
        color: #ffffff;
        font-weight: 600;
        border: none;
        box-shadow: 0 6px 18px rgba(244, 123, 32, 0.35);
        transition: all 0.2s ease;
    }
    .btn-open-store:hover {
        color: #FFFFFF;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(244, 123, 32, 0.45);
    }
</style>
@endsection