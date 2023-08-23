<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noarchive">
    <title> PRIM | Senarai Organisasi</title>

    @include('landing-page.head')
    <style>
        .map-responsive {
            overflow: hidden;
            padding-bottom: 56.25%;
            position: relative;
            height: 0;
        }

        .map-responsive iframe {
            left: 0;
            top: 0;
            height: 100%;
            width: 100%;
            position: absolute;
        }

        #headerhover {
            transform: scale(0.9);
            transition: transform 1s ease;
        }

        #headerhover:hover {
            transform: scale(1.2);
        }

        /* .navbar-area .nav-container .navbar-collapse ul.navbar-nav li.current-menu-item:hover {
            transform: scale(1.0);
        }

        .navbar-area .nav-container .navbar-collapse ul.navbar-nav li:hover {
            transform: scale(1.1);
        } */

        @media only screen and (max-width: 991px){
            .navbar-area .nav-container .navbar-collapse ul.navbar-nav li:hover {
                transform: scale(1.0);
            }

            .navbar-area .nav-container .navbar-collapse ul.navbar-nav li.slash {
                display: none;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-area navbar-expand-lg nav-absolute white nav-style-01">
        <div class="container nav-container">
            <div class="responsive-mobile-menu">
                <div class="logo-wrapper">
                    <a href="/" class="logo">
                        <img src="{{ URL::asset('assets/landing-page/img/logo-header.png') }}" alt="logo">
                    </a>
                </div>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#appside_main_menu"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            {{-- <div class="collapse navbar-collapse" id="appside_main_menu">
                <ul class="navbar-nav">
                    <li><i class="fa fa-arrow-left" aria-hidden="true"><a href="/javascript:history.back()">Back</a></i></li>
                </ul>
            </div> --}}
            <div class="nav-right-content">
                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav">
                        {{-- <li class="current-menu-item">
                            <a href="/">Utama</a>
                        </li> --}}
                        <li><a class="fa fa-arrow-left" href="javascript:history.back()"></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- breadcrumb area start -->
    <div class="breadcrumb-area breadcrumb-bg extra">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb-inner">
                        <h1 class="page-title">Senarai Organisasi</h1>
                        {{-- <ul class="page-navigation">
                        <li><a href="#"> Home</a></li>
                        <li>Blog</li>
                    </ul> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- breadcrumb area end -->

    <div class="page-content-area padding-top-120 padding-bottom-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="main-box clearfix">
                        <div class="table-responsive">
                            <table class="table user-list display nowrap " width="100%" id="tableDerma">
                                <thead>
                                    <tr>
                                        <th class="all"><span>Nama Organisasi</span></th>
                                        {{-- <th class="all"><span>Alamat</span></th>
                                        <th class="all"><span>Email</span></th>
                                        <th class="all"><span>Link</span></th>
                                        <th class="none"><span>Penerangan</span></th>
                                        <th class="none"><span>Action</span></th> --}}
                                    </tr>
                                </thead>
                                {{-- <tbody>
                                    <tr>
                                        <td>
                                            <img src="{{ URL::asset('assets/landing-page/img/building-solid.svg') }}"
                                                alt="">
                                            <a href="/activity-list" class="user-link">Masjid An-Najihah <i class="fas fa-chevron-right"></i></a>
                                            <span class="user-subhead">Ayer Keroh</span>
                                        </td>
                                        <td>
                                            UTeM, Ayer Keroh
                                        </td>

                                        <td>
                                            <a href="#">mila@kunis.com</a>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td>
                                            <img src="{{ URL::asset('assets/landing-page/img/building-solid.svg') }}"
                                                alt="">

                                            <a href="#" class="user-link">Masjid Al-Alami <i class="fas fa-chevron-right"></i></a>
                                            <span class="user-subhead">Ayer Keroh</span>
                                        </td>
                                        <td>
                                            UTeM, Ayer Keroh
                                        </td>

                                        <td>
                                            <a href="#">marlon@brando.com</a>
                                           
                                        </td>

                                    </tr>
                                    <tr>
                                        <td>
                                            <img src="{{ URL::asset('assets/landing-page/img/building-solid.svg') }}"
                                                alt="">

                                            <a href="#" class="user-link">Tahfiz Iman <i class="fas fa-chevron-right"></i></a>
                                            <span class="user-subhead">Bagan Serai</span>
                                        </td>
                                        <td>
                                            Bagan Serai, Perak
                                        </td>

                                        <td>
                                            <a href="#">jack@nicholson</a>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td>
                                            <img src="{{ URL::asset('assets/landing-page/img/building-solid.svg') }}"
                                                alt="">

                                            <a href="#" class="user-link">Pusat Islam UTeM <i class="fas fa-chevron-right"></i></a>
                                            <span class="user-subhead">Ayer Keroh</span>
                                        </td>
                                        <td>
                                            UTeM, Ayer Keroh
                                        </td>

                                        <td>
                                            <a href="#">humphrey@bogart.com</a>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td>
                                            <img src="{{ URL::asset('assets/landing-page/img/building-solid.svg') }}"
                                                alt="">

                                            <a href="#" class="user-link">Surau Telok Mas <i class="fas fa-chevron-right"></i></a>
                                            <span class="user-subhead">Telok Mas</span>
                                        </td>
                                        <td>
                                            UTeM, Ayer Keroh
                                        </td>

                                        <td>
                                            <a href="#">spencer@tracy</a>
                                        </td>

                                    </tr>
                                </tbody> --}}
                            </table>
                        </div>
                        {{-- <ul class="pagination pull-right">
                        <li><a href="#"><i class="fa fa-chevron-left"></i></a></li>
                        <li><a href="#">1</a></li>
                        <li><a href="#">2</a></li>
                        <li><a href="#">3</a></li>
                        <li><a href="#">4</a></li>
                        <li><a href="#">5</a></li>
                        <li><a href="#"><i class="fa fa-chevron-right"></i></a></li>
                    </ul> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- footer area start -->
    <footer class="footer-area">
        <div class="footer-top">
            <div class="container">
                <div class="row justify-content-md-center">
                    <div class="col-lg-3 col-md-6">
                        <div class="footer-widget about_widget">
                            <a href="" style="pointer-events: none;" class="footer-logo"><img
                                    src="{{ URL::asset('assets/landing-page/img/logo-header.png') }}" alt=""></a>
                            <p>Parental Relationship Information Management (PRiM) adalah sebuah sistem untuk
                                menghubungkan ibu bapa serta penjaga dengan pihak sekolah.</p>

                        </div>
                    </div>
                    <div class="col-lg-3 col-md-8">
                        <div class="footer-widget about_widget">
                            <h4 class="widget-title">Hubungi</h4>
                            <p>Email : yahya@utem.edu.my </p>
                            <p>Phone : 60 13-647 7388</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="footer-widget about_widget">
                            <h4 class="widget-title">Alamat</h4>
                            <p> Universiti Teknikal Malaysia Melaka, Hang Tuah Jaya, 76100 Durian Tunggal, Melaka
                            </p>

                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="footer-widget about_widget" style="text-align: center">
                            <a href="#" style="pointer-events: none;" class="footer-logo"><img
                                    src="{{ URL::asset('assets/landing-page/img/logo-utem-white.png') }}" alt=""
                                    style="max-width: 70%"></a>

                            <a href="#" style="pointer-events: none;" class="footer-logo"><img
                                    src="{{ URL::asset('assets/landing-page/img/logo-ftmk.png') }}" alt=""
                                    style="max-width: 70%"></a>

                            <ul class="social-icon" style="text-align: center; ">
                                <li><a href="https://www.facebook.com/MyUTeM/"><i class="fab fa-facebook-f"></i></a>
                                </li>
                                <li><a href="https://www.instagram.com/myutem/"><i class="fab fa-instagram"></i></a>
                                </li>
                                <li><a href="https://twitter.com/myutem"><i class="fab fa-twitter"></i></a></li>
                                <li><a href="https://www.youtube.com/channel/UCmJKvkfmZf4pbXwDqo2sZZg"><i
                                            class="fab fa-youtube"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright-area">
            <!-- copyright area -->
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="copyright-inner">
                            <!-- copyright inner wrapper -->
                            <div class="left-content-area">
                                <!-- left content area -->
                                &copy; Copyrights <span id="year"></span> All rights reserved | PRiM
                            </div><!-- //. left content aera -->
                            <div class="right-content-area">
                                <!-- right content area -->
                                {{-- Designed by <strong>Love</strong> --}}
                            </div><!-- //. right content area -->
                        </div><!-- //.copyright inner wrapper -->
                    </div>
                </div>
            </div>
        </div><!-- //. copyright area -->
    </footer>
    <!-- footer area end -->

    <!-- preloader area start -->
    <div class="preloader-wrapper" id="preloader">
        <div class="preloader">
            <div class="sk-circle">
                <div class="sk-circle1 sk-child"></div>
                <div class="sk-circle2 sk-child"></div>
                <div class="sk-circle3 sk-child"></div>
                <div class="sk-circle4 sk-child"></div>
                <div class="sk-circle5 sk-child"></div>
                <div class="sk-circle6 sk-child"></div>
                <div class="sk-circle7 sk-child"></div>
                <div class="sk-circle8 sk-child"></div>
                <div class="sk-circle9 sk-child"></div>
                <div class="sk-circle10 sk-child"></div>
                <div class="sk-circle11 sk-child"></div>
                <div class="sk-circle12 sk-child"></div>
            </div>
        </div>
    </div>

    <!-- preloader area end -->

    <!-- back to top area start -->
    <div class="back-to-top">
        <i class="fas fa-angle-up"></i>
    </div>
    <!-- back to top area end -->

    <!-- jquery -->
    @include('landing-page.footer-script')


</body>

</html>

<script>
    $(document).ready( function () {
        
        var tableDerma = $('#tableDerma').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
                ajax: {
                    url: "{{ route('landingpage.organization.list') }}",
                    type: 'GET',
                },
                // order: [
                //     [1, 'asc']
                // ],
                // responsive: {
                //     details: {
                //     type: 'column'
                //     }
                // },
                // columnDefs: [{
                //     className: 'control',
                //     orderable: false,
                //     targets: 0  
                // }],
                columns: [{
                    data: "title",
                    name: "title"
                }, {
                    orderable: false,
                    searchable: false
                    
                },]
          });

          $('#btn-show-all-doc').on('click', expandCollapseAll);

            function expandCollapseAll() {
                tableDerma.rows('.parent').nodes().to$().find('td:first-child').trigger('click').length || 
                tableDerma.rows(':not(.parent)').nodes().to$().find('td:first-child').trigger('click')
            }

        // csrf token for ajax
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    });

</script>