<?php
include 'connection.php';
session_start();

if (isset($_SESSION['aghniya_username'])) {
    $user = $_SESSION['aghniya_username'];
    $userid = $_SESSION['aghniya_user_id'];
    include "sidebar.php";
}

// pagination
    $limit = 6;
    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    $sql_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM aghniya_foto
        LEFT JOIN aghniya_user ON aghniya_foto.aghniya_user_id = aghniya_user.aghniya_user_id");

    $count_result = mysqli_fetch_assoc($sql_count);
    $total_records = $count_result['total'];
    $total_pages = ceil($total_records / $limit);

    $sql = mysqli_query($conn, "SELECT DISTINCT aghniya_foto.aghniya_foto_id, aghniya_foto.aghniya_lokasi_file, aghniya_user.aghniya_username
            FROM aghniya_foto
            LEFT JOIN aghniya_user ON aghniya_foto.aghniya_user_id = aghniya_user.aghniya_user_id
            LIMIT $limit OFFSET $offset");

    $sql_total_likes_user = mysqli_query($conn, "
        SELECT COUNT(*) AS total_likes
        FROM aghniya_like_foto
        WHERE aghniya_foto_id IN (SELECT aghniya_foto_id FROM aghniya_foto)
    ");

    $data_likes_user = mysqli_fetch_assoc($sql_total_likes_user);
    $total_likes_user = $data_likes_user['total_likes'];

    $sql_total_comments_user = mysqli_query($conn, "
        SELECT COUNT(aghniya_komentar_foto.aghniya_komentar_id) AS total_comments
        FROM aghniya_komentar_foto
        LEFT JOIN aghniya_foto ON aghniya_komentar_foto.aghniya_foto_id = aghniya_foto.aghniya_foto_id
    ");
    $data_comments_user = mysqli_fetch_assoc($sql_total_comments_user);
    $total_comments_user = $data_comments_user['total_comments'];

// Filter
    if (isset($_GET['filter'])) {
        $filter_album = $_GET['album'];

        if ($filter_album == '0') {
            $total_pages = 0;
        }

        $album_query = mysqli_query($conn, "SELECT aghniya_nama_album FROM aghniya_album WHERE aghniya_album_id = '$filter_album'");

        if (!$album_query) {
            die("Query failed: " . mysqli_error($conn));
        }

        $album_get = mysqli_fetch_assoc($album_query);

        $sql_likes_album = mysqli_query($conn, "
            SELECT COUNT(*) as total_likes
            FROM aghniya_like_foto
            WHERE aghniya_foto_id IN (SELECT aghniya_foto_id FROM aghniya_foto WHERE aghniya_album_id = '$filter_album')
        ");
        $data_likes_album = mysqli_fetch_assoc($sql_likes_album);
        $total_likes_album = $data_likes_album['total_likes'];

        $sql_total_comments_album = mysqli_query($conn, "
            SELECT COUNT(aghniya_komentar_foto.aghniya_komentar_id) AS total_comments
            FROM aghniya_komentar_foto
            LEFT JOIN aghniya_foto ON aghniya_komentar_foto.aghniya_foto_id = aghniya_foto.aghniya_foto_id
            WHERE aghniya_foto.aghniya_album_id = '$filter_album'
        ");
        $data_comments_album = mysqli_fetch_assoc($sql_total_comments_album);
        $total_comments_album = $data_comments_album['total_comments'];

        $sql = mysqli_query($conn, "
            SELECT aghniya_foto.*, aghniya_user.aghniya_username
            FROM aghniya_foto
            LEFT JOIN aghniya_user ON aghniya_foto.aghniya_user_id = aghniya_user.aghniya_user_id
            LEFT JOIN aghniya_album ON aghniya_foto.aghniya_album_id = aghniya_album.aghniya_album_id
            WHERE aghniya_album.aghniya_album_id = '$filter_album'
            LIMIT 1 OFFSET $offset
        ");
    } else {
        $sql = mysqli_query($conn, "SELECT DISTINCT aghniya_foto.aghniya_foto_id, aghniya_foto.aghniya_lokasi_file, aghniya_user.aghniya_username
            FROM aghniya_foto
            LEFT JOIN aghniya_user ON aghniya_foto.aghniya_user_id = aghniya_user.aghniya_user_id
            LIMIT $limit OFFSET $offset
        ");
    }

//data all
    //TOTAL ALL LIKES PUNYA USER
    $sql_total_likes_user = mysqli_query($conn, "
        SELECT COUNT(*) AS total_likes
        FROM aghniya_like_foto
        WHERE aghniya_foto_id IN (SELECT aghniya_foto_id FROM aghniya_foto)
    ");
    $data_likes_user = mysqli_fetch_assoc($sql_total_likes_user);
    $total_likes_user = $data_likes_user['total_likes'];

    //TOTAL ALL COMMENTS PUNYA USER
    $sql_total_comments_user = mysqli_query($conn, "
        SELECT COUNT(aghniya_komentar_foto.aghniya_komentar_id) AS total_comments
        FROM aghniya_komentar_foto
        LEFT JOIN aghniya_foto ON aghniya_komentar_foto.aghniya_foto_id = aghniya_foto.aghniya_foto_id
    ");
    $data_comments_user = mysqli_fetch_assoc($sql_total_comments_user);
    $total_comments_user = $data_comments_user['total_comments'];

    // All filter
        if (isset($_GET['all'])) {
            unset($_GET['album']);

            $sql = mysqli_query($conn, "SELECT DISTINCT aghniya_foto.aghniya_foto_id, aghniya_foto.aghniya_lokasi_file, aghniya_user.aghniya_username
                FROM aghniya_foto
                LEFT JOIN aghniya_user ON aghniya_foto.aghniya_user_id = aghniya_user.aghniya_user_id
                LIMIT $limit OFFSET $offset
            ");
        }

$albums = mysqli_query($conn, "SELECT aghniya_album_id, aghniya_nama_album FROM aghniya_album");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report</title>
    <link href="./src/output.css" rel="stylesheet">
    <style>
        @media print {
            #default-sidebar {
                display: none;
            }

            #report {
                width: 100%;
                margin-left: 0;
            }

            #filter_album{
                display: none;
            }

            #pagination_report {
                display: none;
            }

            #print_info {
                display: block;
                margin-top: 10%;
            }

            #title_report {
                display: block;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="p-12 sm:ml-64" id="report">
        <div class="text-2xl font-semibold" id="title_report">Report</div>

        <div class="py-4" id="filter_album">
            <div class="text-lg justify-center content-center py-auto my-auto">
                Filter Berdasarkan Album:
                
            </div>
            <form action="report_admin.php" method="GET">
                <div class="w-full">
                    <div class="flex mt-3 gap-5 text-gray-800">
                        <div class="w-3/5">
                            <select name="album" class="border border-1 w-full px-2 py-2 mx-2 rounded-lg">
                                <!-- <option value="all" <?php echo !isset($_GET['album']) ? 'selected' : ''; ?>>Semua Album</option> -->
                                <?php while ($album = mysqli_fetch_assoc($albums)) { ?>
                                    <option value="<?=$album['aghniya_album_id']?>" <?php echo (isset($_GET['album']) && $_GET['album'] == $album['aghniya_album_id']) ? 'selected' : ''; ?>><?=$album['aghniya_nama_album']?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="w-1/3 flex">
                            <button type="submit" name="filter" class="border border-1 w-1/2 px-1 py-2 mx-2 rounded-lg bg-gray-800 text-white text-xs font-bold">Filter</button>
                            <button type="submit" name="all" class="border border-1 w-1/2 px-1 py-2 mx-2 rounded-lg bg-gray-800 text-white text-xs font-bold">All</button>
                            <button type="button"  onclick="printPage()" name="cetak" class="border border-1 w-1/2 px-1 py-2 mx-2 rounded-lg bg-gray-800 text-white text-xs font-bold">Cetak</button>
                        </div>
                    </div>
                </div>      
            </form>
        </div>

        <div class="">
            <div class="flex flex-wrap gap-8 sm:gap-12 content-center justify-center my-14">
                <?php while($data = mysqli_fetch_array($sql)) { 
                    $foto = $data['aghniya_foto_id'];
                    $sql_likes = mysqli_query($conn, "SELECT aghniya_like_id FROM aghniya_like_foto WHERE aghniya_foto_id = $foto");
                    $data_likes = mysqli_num_rows($sql_likes);

                    $sql_comment = mysqli_query($conn, "SELECT aghniya_komentar_id FROM aghniya_komentar_foto WHERE aghniya_foto_id = $foto");
                    $data_comment = mysqli_num_rows($sql_comment);

                    $liked = mysqli_query($conn, "SELECT * FROM aghniya_like_foto WHERE aghniya_foto_id = $foto AND aghniya_user_id = $userid");
                ?>
                    <a href="photo_detail_user.php?photo_id=<?=$data['aghniya_foto_id']?>" class="bg-gray-100 border border-2 p-2 xs:w-full sm:w-1/2 md:w-1/3 lg:w-1/4 border-gray-200 rounded-lg shadow-md">
                        <div class="w-full border border-2 border-gray-200 border-opacity-80 h-48 rounded-lg">
                            <img src="<?=$data['aghniya_lokasi_file']?>" alt="" class="w-full h-full object-cover rounded-md">
                        </div>
                        <div class="flex">
                            <div class="w-1/3 justify-start my-auto">
                                <div class="text-sm font-semibold px-1 text-wrap"><?=$data['aghniya_username']?></div>
                            </div>

                            <form action="cek_likes_user.php" method="POST" class="w-1/3">
                                <input type ="hidden" value="<?=$data['aghniya_foto_id']?>" name="id_photo">
                                <input type="hidden" value="<?=$userid?>" name="id_user">
                                <input type="hidden" value="<?=$userid?>" name="id_user_photo">
                                <input type="hidden" value="your_photo.php?id_photo=<?=$data['aghniya_foto_id']?>" name="direction_path">

                                <div class="w-full mx-auto my-auto">
                                    <div class="flex my-3 text-sm">
                                        <div class="w-1/2 my-auto">
                                            <button type="submit" name="likes" class="p-2">
                                                <?php
                                                    if (mysqli_num_rows($liked) == 0 ){
                                                ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-heart text-sm" viewBox="0 0 16 16">
                                                        <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15"/>
                                                    </svg>
                                                <?php } else { ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#990000" class="bi bi-heart-fill" viewBox="0 0 16 16">
                                                        <path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314"/>
                                                    </svg>
                                                <?php } ?>
                                            </button>
                                        </div>
                                        <div class="w-1/2 mx-auto my-auto text-sm">
                                            <?=$data_likes?>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="w-1/5 mx-auto my-auto">
                                <div class="flex my-3 mx-auto my-auto">
                                    <div class="my-auto">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chat" viewBox="0 0 16 16">
                                            <path d="M2.678 11.894a1 1 0 0 1 .287.801 11 11 0 0 1-.398 2c1.395-.323 2.247-.697 2.634-.893a1 1 0 0 1 .71-.074A8 8 0 0 0 8 14c3.996 0 7-2.807 7-6s-3.004-6-7-6-7 2.808-7 6c0 1.468.617 2.83 1.678 3.894m-.493 3.905a22 22 0 0 1-.713.129c-.2.032-.352-.176-.273-.362a10 10 0 0 0 .244-.637l.003-.01c.248-.72.45-1.548.524-2.319C.743 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7-3.582 7-8 7a9 9 0 0 1-2.347-.306c-.52.263-1.639.742-3.468 1.105"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3 my-auto">
                                        <?=$data_comment?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php } ?>
            </div>

                <div id="print_info">
                    <?php if (isset($total_likes_album)): ?>
                    <div class="text-lg font-semibold my-4">Data</div>
                        <div class="text-md font-semibold mt-6">
                            <table class="">
                                <tr>
                                    <td class="px-3">Nama Album</td>
                                    <td class="px-3">:</td>
                                    <td class="px-3"><?=$album_get['aghniya_nama_album']?></td>
                                </tr>
                                <tr>
                                    <td class="px-3">Jumlah Seluruh Like</td>
                                    <td class="px-3">:</td>
                                    <td class="px-3"><?=$total_likes_album?> Likes</td>
                                </tr>
                                <tr>
                                    <td class="px-3">Jumlah Seluruh Komentar</td>
                                    <td class="px-3">:</td>
                                    <td class="px-3"><?=$total_comments_album?> Komentar</td>
                                </tr>
                            </table>
                            <!-- Jumlah Like pada album ini adalah: <?= $total_likes_album ?> likes -->
                        </div>
                    <?php endif; ?>
                </div>

                <div id="print_info_all" style="display: <?= isset($_GET['album']) ? 'none' : 'block'; ?>;">
                    <div class="text-lg font-semibold my-4">Data</div>
                    <div class="text-md font-semibold mt-6">
                        <table class="">
                            <tr>
                                <td class="px-3">All Album</td>
                            </tr>
                            <tr>
                                <td class="px-3">Jumlah Seluruh Like</td>
                                <td class="px-3">:</td>
                                <td class="px-3"><?=$total_likes_user?> Likes</td>
                            </tr>
                            <tr>
                                <td class="px-3">Jumlah Seluruh Komentar</td>
                                <td class="px-3">:</td>
                                <td class="px-3"><?=$total_comments_user?> Komentar</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <?php 
                $usn = $_SESSION['aghniya_user_id'];
                $sql = mysqli_query($conn, "SELECT * FROM aghniya_user WHERE aghniya_user_id = $usn");
                $dataa = mysqli_fetch_assoc($sql);
            ?>

            <div class="mt-7 absolute right-0" style="margin-top:100px; position: absolute; right: 0;">
                <div class="font-semibold">Cimahi, <?=date('Y-m-d')?></div>
                <div class="h-20 border-b" style="width:150px; height:60px"></div>
                <div class="font-semibold"><?=$dataa['aghniya_nama_lengkap']?></div>
            </div>

        </div>

    <div class="flex justify-center mt-6" id="pagination_report">
    <?php if (empty($_GET['filter'])) { ?>

        <?php if ($total_pages > 0) { ?>
            <?php 
                if (isset($_GET['album'])) {
                    $filter = $_GET['album'];
                    $filter = '&filter=';
                } else {
                    $filter = '&all=';
                }
            ?>

            <?php if ($filter === '&all='){ ?>
            <div class="flex gap-4">
                <a href="report_admin.php?album=<?=isset($_GET['album']) ? $_GET['album'] : ''?>&page=<?= max(1, $page - 1) ?><?=$filter?>"
                    class="<?= $page == 1 ? 'disabled' : '' ?> flex items-center justify-center px-3 h-8 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-xl hover:bg-gray-100 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="mr-3 bi bi-arrow-left-circle" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/>
                    </svg>
                    Previous
                </a>

                <a href="report_admin.php?album=<?=isset($_GET['album']) ? $_GET['album'] : ''?>&page=<?= min($page + 1, $total_pages) ?><?=$filter?>"
                    class="<?= $page == $total_pages ? 'disabled' : '' ?> flex items-center justify-center px-3 h-8 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-xl hover:bg-gray-100 hover:text-gray-700">
                    Next
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ml-3 bi bi-arrow-right-circle" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0M4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5z"/>
                    </svg>
                </a>
            </div>
            <?php } ?>
        <?php } ?>
    <?php } ?>
</div>


    <script>
        function printPage() {
            window.print();
        }
    </script>
</body>
</html>
