<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="icon" href="https://rsudrtnotopuro.sidoarjokab.go.id/assets/content/filemanager/source/new-file/logo_rsud_baru.png?v=1734998400040" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="theme-color" content="#03a9f4" />
    <meta name="description" content="Sistem Antrian Farmasi 1 RSUD R.T. Notopuro Sidoarjo" />
    <link rel="apple-touch-icon" href="./icon.png" />
    <title>Antrian Farmasi 1 RSUD R.T. Notopuro Sidoarjo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'green-hospital': '#2c5530',
                        'green-light': '#28a745',
                        'green-bg': '#e8f5e8'
                    }
                }
            }
        }
    </script>
    <style>
        .bg-medical-pattern {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://antrian-farmasi.rsudsidoarjo.co.id/static/media/background-1920x1080.ebb3f0dd.jpg');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body class="font-sans bg-medical-pattern bg-cover bg-center min-h-screen flex flex-col items-center justify-center text-white"><noscript>You need to enable JavaScript to run this app.</noscript>
    <div id="root">
        <div class="text-center w-11/12 p-8 md:p-10 bg-white bg-opacity-95 rounded-3xl shadow-2xl text-gray-800">
            <div class="mb-8">
                <h1 class="text-3xl md:text-4xl font-bold text-green-hospital mb-3 drop-shadow-sm">FARMASI 1</h1>
                <h2 class="text-xl md:text-3xl text-green-hospital mb-2 font-bold">RSUD R.T. NOTOPURO</h2>
                <h3 class="text-lg md:text-2xl text-green-hospital mb-4 font-bold">SIDOARJO</h3>
                <div class="text-base md:text-lg text-gray-600 mb-8 font-medium" id="datetime">
                    <?php
                    date_default_timezone_set('Asia/Jakarta');
                    echo date('l, d F Y, H:i:s') . ' WIB';
                    ?>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl mb-8 border-l-4 border-green-light">
                <p class="text-lg text-gray-800 m-0">Silahkan ambil nomor antrian berikut</p>
            </div>

            <div class="bg-green-bg p-6 md:p-8 rounded-2xl mb-8 border-2 border-green-light">
                <h2 class="text-xl md:text-3xl text-green-light mb-4 font-bold">AMBIL NOMOR ANTRIAN</h2>
                <div class="mb-5">
                    <p class="text-base text-gray-600 mb-2">ANTRIAN TERAKHIR:</p>
                    <div class="text-4xl md:text-5xl font-bold text-green-hospital my-4 drop-shadow-sm" id="currentNumber">F048</div>
                </div>

                <button class="bg-gradient-to-r from-green-light to-teal-500 text-white border-0 px-8 py-5 md:px-10 md:py-6 text-lg md:text-xl font-bold rounded-full cursor-pointer transition-all duration-300 shadow-lg hover:from-green-600 hover:to-teal-600 hover:-translate-y-1 hover:shadow-xl active:translate-y-0 disabled:bg-gray-500 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none uppercase tracking-wider" id="takeNumberBtn" onclick="takeNumber()">
                    Ambil Nomor Antrian
                </button>

                <div class="hidden mt-5" id="loading">
                    <div class="border-4 border-gray-300 border-t-green-light rounded-full w-10 h-10 animate-spin-custom mx-auto"></div>
                    <p class="mt-2" id="loadingText">Sedang memproses...</p>
                </div>

                <div class="hidden bg-blue-100 text-blue-800 p-4 rounded-xl mt-5 border border-blue-300" id="numberTakenMessage">
                    <strong>Nomor Antrian Anda:</strong> <span id="takenNumber" class="text-2xl font-bold"></span><br>
                    <span class="text-sm">Silahkan klik tombol "Cetak Antrian" untuk mencetak tiket Anda.</span>
                </div>

                <div class="hidden bg-green-100 text-green-800 p-4 rounded-xl mt-5 border border-green-300" id="successMessage">
                    <strong>Berhasil!</strong> Nomor antrian telah dicetak 2x. Silahkan ambil tiket Anda.
                </div>

                <div class="hidden bg-red-100 text-red-800 p-4 rounded-xl mt-5 border border-red-300" id="errorMessage">
                    <strong>Error!</strong> <span id="errorText">Gagal memproses antrian. Silahkan coba lagi.</span>
                </div>
            </div>
        </div>
    </div>
    <script>
        ! function(e) {
            function r(r) {
                for (var n, a, i = r[0], c = r[1], l = r[2], f = 0, p = []; f < i.length; f++) a = i[f], Object.prototype.hasOwnProperty.call(o, a) && o[a] && p.push(o[a][0]), o[a] = 0;
                for (n in c) Object.prototype.hasOwnProperty.call(c, n) && (e[n] = c[n]);
                for (s && s(r); p.length;) p.shift()();
                return u.push.apply(u, l || []), t()
            }

            function t() {
                for (var e, r = 0; r < u.length; r++) {
                    for (var t = u[r], n = !0, i = 1; i < t.length; i++) {
                        var c = t[i];
                        0 !== o[c] && (n = !1)
                    }
                    n && (u.splice(r--, 1), e = a(a.s = t[0]))
                }
                return e
            }
            var n = {},
                o = {
                    1: 0
                },
                u = [];

            function a(r) {
                if (n[r]) return n[r].exports;
                var t = n[r] = {
                    i: r,
                    l: !1,
                    exports: {}
                };
                return e[r].call(t.exports, t, t.exports, a), t.l = !0, t.exports
            }
            a.e = function(e) {
                var r = [],
                    t = o[e];
                if (0 !== t)
                    if (t) r.push(t[2]);
                    else {
                        var n = new Promise((function(r, n) {
                            t = o[e] = [r, n]
                        }));
                        r.push(t[2] = n);
                        var u, i = document.createElement("script");
                        i.charset = "utf-8", i.timeout = 120, a.nc && i.setAttribute("nonce", a.nc), i.src = function(e) {
                            return a.p + "static/js/" + ({} [e] || e) + "." + {
                                3: "8f64dda8"
                            } [e] + ".chunk.js"
                        }(e);
                        var c = new Error;
                        u = function(r) {
                            i.onerror = i.onload = null, clearTimeout(l);
                            var t = o[e];
                            if (0 !== t) {
                                if (t) {
                                    var n = r && ("load" === r.type ? "missing" : r.type),
                                        u = r && r.target && r.target.src;
                                    c.message = "Loading chunk " + e + " failed.\n(" + n + ": " + u + ")", c.name = "ChunkLoadError", c.type = n, c.request = u, t[1](c)
                                }
                                o[e] = void 0
                            }
                        };
                        var l = setTimeout((function() {
                            u({
                                type: "timeout",
                                target: i
                            })
                        }), 12e4);
                        i.onerror = i.onload = u, document.head.appendChild(i)
                    } return Promise.all(r)
            }, a.m = e, a.c = n, a.d = function(e, r, t) {
                a.o(e, r) || Object.defineProperty(e, r, {
                    enumerable: !0,
                    get: t
                })
            }, a.r = function(e) {
                "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(e, Symbol.toStringTag, {
                    value: "Module"
                }), Object.defineProperty(e, "__esModule", {
                    value: !0
                })
            }, a.t = function(e, r) {
                if (1 & r && (e = a(e)), 8 & r) return e;
                if (4 & r && "object" == typeof e && e && e.__esModule) return e;
                var t = Object.create(null);
                if (a.r(t), Object.defineProperty(t, "default", {
                        enumerable: !0,
                        value: e
                    }), 2 & r && "string" != typeof e)
                    for (var n in e) a.d(t, n, function(r) {
                        return e[r]
                    }.bind(null, n));
                return t
            }, a.n = function(e) {
                var r = e && e.__esModule ? function() {
                    return e.default
                } : function() {
                    return e
                };
                return a.d(r, "a", r), r
            }, a.o = function(e, r) {
                return Object.prototype.hasOwnProperty.call(e, r)
            }, a.p = "/", a.oe = function(e) {
                throw console.error(e), e
            };
            var i = this["webpackJsonpantrian-rsud-sidoarjo"] = this["webpackJsonpantrian-rsud-sidoarjo"] || [],
                c = i.push.bind(i);
            i.push = r, i = i.slice();
            for (var l = 0; l < i.length; l++) r(i[l]);
            var s = c;
            t()
        }([]);

        // Queue Management System with Database Integration

        // Update datetime every second
        function updateDateTime() {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: 'Asia/Jakarta'
            };

            const formatter = new Intl.DateTimeFormat('id-ID', options);
            const formattedDate = formatter.format(now) + ' WIB';
            document.getElementById('datetime').textContent = formattedDate;
        }

        // Check for daily reset
        async function checkDailyReset() {
            try {
                const response = await fetch('./api/check_and_reset_daily.php');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    console.log('Daily reset check:', data.message);
                    console.log('Current date:', data.current_date);
                    console.log('Next number will be:', data.next_number);
                    
                    // Update current number display if no queues exist for today
                    if (!data.queue_exists) {
                        document.getElementById('currentNumber').textContent = 'F000';
                    }
                } else {
                    console.error('Daily reset check failed:', data.error);
                }
            } catch (error) {
                console.error('Error checking daily reset:', error);
            }
        }

        // Load current queue number from database
        async function loadCurrentNumber() {
            try {
                const response = await fetch('./api/get_current_number.php');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('currentNumber').textContent = data.currentNumber;
                } else {
                    console.error('API returned error:', data.error);
                }
            } catch (error) {
                console.error('Error loading current number:', error);
                // Set default value if API fails
                document.getElementById('currentNumber').textContent = 'F000';
            }
        }

        // Update datetime immediately and then every second
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Check for daily reset and load current number on page load
        checkDailyReset();
        loadCurrentNumber();

        async function takeNumber() {
            const btn = document.getElementById('takeNumberBtn');
            const loading = document.getElementById('loading');
            const successMsg = document.getElementById('successMessage');
            const errorMsg = document.getElementById('errorMessage');
            const currentNumberEl = document.getElementById('currentNumber');

            // Reset messages
            successMsg.style.display = 'none';
            errorMsg.style.display = 'none';

            // Show loading
            btn.disabled = true;
            loading.style.display = 'block';

            try {
                // Call API to generate new queue number and print
                const response = await fetch('./api/take_number.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({})
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    // Update display with new number
                    currentNumberEl.textContent = data.queueNumber;

                    // Show success message
                    loading.style.display = 'none';
                    
                    if (data.printSuccess) {
                        successMsg.textContent = 'Berhasil! Nomor antrian telah dicetak 2x. Silahkan ambil tiket Anda.';
                        successMsg.style.display = 'block';
                    } else {
                        // Show detailed print error information
                        let errorDetails = '';
                        if (data.printDetails) {
                            if (!data.printDetails.first_print.success) {
                                const error1 = data.printDetails.first_print.error || 'Unknown error';
                                const httpCode1 = data.printDetails.first_print.http_code || 'N/A';
                                errorDetails += `Print 1: ${error1} (HTTP: ${httpCode1}). `;
                            }
                            if (!data.printDetails.second_print.success) {
                                const error2 = data.printDetails.second_print.error || 'Unknown error';
                                const httpCode2 = data.printDetails.second_print.http_code || 'N/A';
                                errorDetails += `Print 2: ${error2} (HTTP: ${httpCode2}).`;
                            }
                        }
                        
                        successMsg.textContent = 'Nomor antrian berhasil dibuat (' + data.queueNumber + '), namun ada masalah dengan printer. ' + errorDetails;
                        successMsg.style.display = 'block';
                        
                        // Log detailed print information for debugging
                        console.log('Print Details:', data.printDetails);
                    }

                    // Re-enable button after 3 seconds
                    setTimeout(() => {
                        btn.disabled = false;
                        successMsg.style.display = 'none';
                    }, 3000);
                } else {
                    throw new Error(data.error || 'Unknown error occurred');
                }

            } catch (error) {
                // Show error message
                loading.style.display = 'none';
                errorMsg.textContent = 'Error! Gagal mengambil nomor antrian: ' + error.message;
                errorMsg.style.display = 'block';

                // Re-enable button after 3 seconds
                setTimeout(() => {
                    btn.disabled = false;
                    errorMsg.style.display = 'none';
                }, 3000);
            }
        }

        // Keyboard shortcut (Space or Enter to take number)
        document.addEventListener('keydown', function(event) {
            if ((event.code === 'Space' || event.code === 'Enter') && !document.getElementById('takeNumberBtn').disabled) {
                event.preventDefault();
                takeNumber();
            }
        });
    </script>
    <script src="/static/js/2.60e3a0a3.chunk.js"></script>
    <script src="/static/js/main.90e7a248.chunk.js"></script>
</body>

</html>