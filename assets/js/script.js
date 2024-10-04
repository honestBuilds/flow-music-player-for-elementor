// // script.js

// jQuery(document).ready(function ($) {
//     // Play/Pause button functionality
//     $(document).on('click', '.play-button', function () {
//         var audio = $(this).siblings('audio')[0];
//         if (audio.paused) {
//             audio.play();
//             $(this).text('Pause');
//         } else {
//             audio.pause();
//             $(this).text('Play');
//         }
//     });

//     // Next button functionality
//     $(document).on('click', '.next-button', function () {
//         var playlist = $(this).closest('.flow-audio-playlist-widget');
//         var current = playlist.find('audio')[0];
//         var next = $(current).next('audio')[0];
//         if (next) {
//             current.pause();
//             current.currentTime = 0;
//             next.play();
//         }
//     });

//     // Previous button functionality
//     $(document).on('click', '.prev-button', function () {
//         var playlist = $(this).closest('.flow-audio-playlist-widget');
//         var current = playlist.find('audio')[0];
//         var prev = $(current).prev('audio')[0];
//         if (prev) {
//             current.pause();
//             current.currentTime = 0;
//             prev.play();
//         }
//     });

//     // Volume control functionality
//     $(document).on('input', '.volume-slider', function () {
//         var volume = $(this).val();
//         $(this).siblings('audio')[0].volume = volume / 100;
//     });
// });
