<div id="flow-audio-playlist-body">

    <!-- Mobile Player -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-gray-900 flex flex-col z-20 shadow-lg text-white font-sans">
        <input id="progress-bar" type="range" min="0" max="100" value="0" style="display: none;">
        <!-- <div class="progress-bar w-full h-1 bg-gradient-to-r from-blue-500 to-purple-500" style="width: 0%;"></div> -->
        <div class="flex items-center justify-between p-3">
            <div class="flex items-center">
                <img id="coverArt" src="{{{settings.cover_art.url}}}" alt="Cover Art" class="w-12 h-12 rounded-md mr-3">
                <div>
                    <p class="font-semibold text-sm current-song-title m-track-title"></p>
                    <p class="text-xs text-gray-400 artist-name">{{{settings.playlist_artist}}}</p>
                </div>
            </div>
            <div class="flex items-end">
                <button class="mr-4 prev-button">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button class="play-button bg-white text-black p-[17px] rounded-full overflow-hidden w-12 h-12">
                    <img src="/wp-content/plugins/flow-elementor-widgets/widgets/audio_playlist/src/play-btn.svg" alt="Play Button" class="object-cover">
                </button>
                <button class="ml-4 next-button">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>

    </div>

    <div class="fixed inset-0 bg-cover bg-center bg-no-repeat blur-3xl opacity-30 cover-art"
        style="background-image: url('{{{settings.cover_art.url}}}');"></div>
    <div class="container mx-auto px-4 py-8 relative z-10">
        <div class="flex flex-col md:flex-row">
            <div id="cover" class="md:w-2/5 mb-8 md:mb-0 text-center md:sticky md:top-8 md:self-start">
                <div class="w-[300px] h-[300px] rounded-lg shadow-lg bg-gray-800 mx-auto bg-cover bg-no-repeat bg-center cover-art mb-5"
                    style="background-image: url('{{{settings.cover_art.url}}}'); box-shadow: 0 0 30px rgba(255, 255, 255, 0.3);"
                    alt="Cover Art"></div>

                <h1 class="album-title text-2xl font-bold mt-11">{{{settings.playlist_title}}}</h1>
                <p class="text-gray-400 artist-name no-mbe">{{{settings.playlist_artist}}}</p>
                <p class="text-gray-400 album-info no-mbe"></p>
                <p class="text-gray-400 album-stats no-mbe"></p>
                <!-- controls -->
                <div class="flex space-x-4 mt-4 justify-center items-center">
                    <button class="bg-gray-800 p-2 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg></button>
                    <button class="bg-gray-800 p-2 rounded-full prev-button"><svg class="w-5 h-5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg></button>
                    <button class="play-button bg-white text-black p-[17px] rounded-full overflow-hidden w-12 h-12">
                        <img src="/wp-content/plugins/flow-elementor-widgets/widgets/audio_playlist/src/play-btn.svg" alt="Play Button" class="object-cover">
                    </button>
                    <button class="bg-gray-800 p-2 rounded-full next-button"><svg class="w-5 h-5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg></button>
                    <button class="bg-gray-800 p-2 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg></button>
                </div>
            </div>
            <div id="songList" class="md:w-3/5 md:pl-8 p-2">
                <!-- Song list will be dynamically populated here -->
            </div>
        </div>
    </div>
</div>