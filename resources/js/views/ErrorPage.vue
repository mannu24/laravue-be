<template>
	<section class="flex items-center min-h-[80vh]">
		<div class="container flex flex-col items-center justify-center px-5 mx-auto my-8">
			<div class="max-w-md text-center">
				<h2 class="mb-8 font-extrabold text-9xl text-gray-300 tracking-widest">
					<span class="sr-only">Error</span>404
				</h2>
				<p class="text-2xl font-semibold md:text-3xl text-white">Sorry, we couldn't find this page.</p>
				<p class="mt-4 mb-8 text-gray-400">But dont worry, you can find plenty of other things on our homepage.</p>
                <router-link to="/" class="w-full bg-vue/90 hover:bg-vue inline-flex transition-all duration-300 justify-center whitespace-nowrap rounded-lg px-3.5 py-2.5 text-sm font-medium text-white shadow-sm shadow-vue/10 focus:outline-none focus:bg-vue focus:ring focus:ring-vue/40 focus-visible:outline-none focus-visible:ring focus-visible:ring-vue/40">Back to Home</router-link>
			</div>
		</div>
	</section>
</template>