<!doctype html>
<html lang="id">
<head>
    {{-- Meta --}}
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Icon --}}
    <link rel="icon" href="/logo.png" type="image/x-icon" />

    {{-- Judul --}}
    <title>Laravel Catatan Keuangan</title>

    {{-- Styles --}}
    @livewireStyles
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- SweetAlert2 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body class="bg-light">
    
    {{-- NAVIGASI --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('app.home') }}">Catatan Keuangan App</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('app.home') ? 'active' : '' }}" href="{{ route('app.home') }}">
                            Catatan Keuangan
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link disabled text-white-50">{{ Auth::user()->name }}</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="{{ route('auth.logout') }}">
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        @yield('content')
    </div>

    {{-- SCRIPTS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    {{-- SweetAlert2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- ApexCharts JS --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    {{-- Moment.js CDN --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    
    @livewireScripts

    {{-- 3. SCRIPT KONEKTOR LIVEWIRE/BOOTSTRAP & SWEETALERT & CHART --}}
    <script>
        const BATCH_BOOTSTRAP = window.bootstrap; 
        
        let chartInstance1 = null; // Monthly Chart Instance
        let chartInstance2 = null; // Cumulative Chart Instance

        // FUNGSI INIT CHART 1 (Monthly Mixed Chart: Bar + Line)
        function initMonthlyChart(chartData) {
            const containerId = window.chartContainerId1 || 'monthlyChart';
            const chartContainer = document.getElementById(containerId);

            if (!chartContainer || !chartData || chartData.categories.length === 0) {
                 if (chartInstance1) { chartInstance1.destroy(); chartInstance1 = null; }
                return; 
            }
            if (chartInstance1) { chartInstance1.destroy(); chartInstance1 = null; }

            // Pisahkan data menjadi Bar (1 & 2) dan Line (3)
            var seriesOptions = [
                {
                    name: chartData.series[0].name, // Total Pemasukan
                    type: 'bar',
                    data: chartData.series[0].data
                },
                {
                    name: chartData.series[1].name, // Total Pengeluaran
                    type: 'bar',
                    data: chartData.series[1].data
                },
                {
                    name: chartData.series[2].name, // Saldo Akhir
                    type: 'line',
                    data: chartData.series[2].data
                }
            ];

            var options = {
                series: seriesOptions, 
                chart: {
                    type: 'bar', 
                    height: 350,
                    stacked: false, 
                    toolbar: { show: true }
                },
                stroke: { 
                    width: [1, 1, 4], 
                    curve: 'smooth' 
                },
                dataLabels: { enabled: false },
                plotOptions: { bar: { horizontal: false, columnWidth: '50%' } },
                xaxis: { categories: chartData.categories },
                fill: { opacity: [1, 1, 0.8] },
                colors: ['#008FFB', '#FF4560', '#00E396'], 
                yaxis: {
                    labels: {
                        formatter: (val) => {
                            if (val >= 1000000) return (val / 1000000).toFixed(1) + ' Jt';
                            return val / 1000 + 'K';
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: (val) => {
                            return 'Rp ' + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        }
                    }
                },
                legend: { position: 'top', horizontalAlign: 'left' }
            };

            chartInstance1 = new ApexCharts(chartContainer, options);
            chartInstance1.render();
        }

        // FUNGSI INIT CHART 2 (Cumulative Area Chart)
        function initCumulativeChart(chartData) {
            const containerId = window.chartContainerId2 || 'cumulativeChart';
            const chartContainer = document.getElementById(containerId);

            if (!chartContainer || !chartData || chartData.series[0].data.length <= 1) {
                 if (chartInstance2) { chartInstance2.destroy(); chartInstance2 = null; }
                return;
            }
            if (chartInstance2) { chartInstance2.destroy(); chartInstance2 = null; }

            var options = {
                series: chartData.series, 
                chart: {
                    type: 'line', 
                    height: 350,
                    stacked: false,
                    zoom: { enabled: false },
                },
                stroke: { curve: 'stepline', width: 2 }, 
                dataLabels: { enabled: false },
                markers: { size: 4 }, 
                fill: { opacity: 1 },
                colors: ['#00E396'], 
                
                xaxis: {
                    type: 'category', 
                    categories: chartData.categories, 
                    labels: {
                        rotate: -45, 
                        rotateAlways: true,
                        trim: false
                    },
                    tickPlacement: 'on'
                },

                yaxis: {
                    labels: {
                        style: { colors: '#8e8da4' },
                        offsetX: 0,
                        formatter: function(val) {
                            return (val / 1000000).toFixed(2) + ' Jt';
                        },
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                title: {
                    text: 'Saldo Kumulatif Berdasarkan Transaksi',
                    align: 'left',
                    offsetX: 14
                },
                tooltip: {
                    shared: true,
                    x: {
                        show: true,
                        formatter: function(val) {
                            return val; 
                        }
                    },
                    y: {
                        formatter: function(val) {
                            return 'Rp ' + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        }
                    }
                },
                legend: { position: 'top', horizontalAlign: 'right', offsetX: -10 }
            };

            chartInstance2 = new ApexCharts(chartContainer, options);
            chartInstance2.render();
        }

        document.addEventListener("livewire:initialized", () => {
            // PANGGIL INIALISASI AWAL
            initMonthlyChart(window.financialChartData1);
            initCumulativeChart(window.financialChartData2); 
            
            // LISTENER OTOMATIS UNTUK MEMPERBARUI KEDUA CHART
            Livewire.on("chartDataUpdated", (event) => {
                const monthlyData = event.monthly;
                const cumulativeData = event.cumulative; 

                // --- UPDATE CHART 1 (Monthly) ---
                if (chartInstance1) {
                    // Update series harus dikirim dalam format yang sama seperti init
                    chartInstance1.updateOptions({
                        series: [
                            { name: monthlyData.series[0].name, type: 'bar', data: monthlyData.series[0].data },
                            { name: monthlyData.series[1].name, type: 'bar', data: monthlyData.series[1].data },
                            { name: monthlyData.series[2].name, type: 'line', data: monthlyData.series[2].data }
                        ],
                        xaxis: { categories: monthlyData.categories }
                    });
                } else if (monthlyData) {
                    initMonthlyChart(monthlyData);
                }

                // --- UPDATE CHART 2 (Cumulative) ---
                if (chartInstance2) {
                    chartInstance2.updateSeries(cumulativeData.series); 
                    chartInstance2.updateOptions({ xaxis: { categories: cumulativeData.categories } });
                } else if (cumulativeData) {
                    initCumulativeChart(cumulativeData);
                }
            });

            // LIVEWIRE/BOOTSTRAP MODAL HANDLERS
            Livewire.on("closeModal", (data) => {
                if (data && data.id && BATCH_BOOTSTRAP) { 
                    const modal = BATCH_BOOTSTRAP.Modal.getInstance(
                        document.getElementById(data.id)
                    );
                    if (modal) modal.hide();
                }
            });

            Livewire.on("showModal", (data) => {
                if (data && data.id && BATCH_BOOTSTRAP) { 
                    const modal = BATCH_BOOTSTRAP.Modal.getOrCreateInstance(
                        document.getElementById(data.id)
                    );
                    if (modal) modal.show();
                }
            });

            // SWEETALERT LISTENERS
            Livewire.on("simpleSuccess", (data) => {
                Swal.fire({
                  position: "top-end",
                  icon: "success",
                  title: data.text || "Your work has been saved", 
                  showConfirmButton: false,
                  timer: 1500
                });
            });
            
            Livewire.on("confirmDelete", (data) => {
                const recordId = data.id;

                Swal.fire({
                    title: "Are you sure?", 
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6", 
                    cancelButtonColor: "#d33",   
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel!", 
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.Livewire.dispatch('executeDelete', { recordId: recordId });
                        
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        Swal.fire({
                            title: "Cancelled",
                            text: "Catatan keuangan Anda aman.",
                            icon: "error"
                        });
                    }
                });
            });
            
            Livewire.on("recordDeleted", () => {
                Swal.fire({
                    title: "Deleted!",
                    text: "Catatan keuangan berhasil dihapus.",
                    icon: "success"
                });
            });

            Livewire.on("deleteError", (data) => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    icon: 'error',
                    title: data.title || 'Gagal',
                    text: data.message,
                });
            });
            
        });
    </script>
</body>
</html>