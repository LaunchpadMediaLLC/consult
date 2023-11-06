<?php 

	get_header(); 
	// Get the current user ID
	$current_user_id = get_current_user_id();

?>
<main>
	<header class="sticky top-0 z-30 w-full bg-white">
	    <div class="bg-background">
	        <div class="container mx-auto">
	            <div class="text-center md:text-right">
	                <?php if (is_user_logged_in()) : ?>
	                	<div class="text-sm font-bold py-2 px-4">
	                		<?php if (is_user_mod()) : ?>
	                			<div class="space-x-2 divide-x">
	                				<span class="text-blue"><?php echo esc_html(get_the_author_meta('display_name', $current_user_id)); ?></span>
		                        	<a href="/moderator-dashboard" class="inline-block pl-4 hover:text-green transition-all duration-200 ease-in-out">Dashboard</a>
		                        	<a href="<?php echo wp_logout_url(); ?>" class="inline-block pl-4 hover:text-green transition-all duration-200 ease-in-out">Log Out</a>
		                        </div>
		                    <?php else : ?>
		                    	<div class="space-x-2 divide-x">
		                    		<span class="text-blue"><?php echo esc_html(get_the_author_meta('display_name', $current_user_id)); ?></span>
		                        	<a href="/my-consultations" class="inline-block pl-4 hover:text-green transition-all duration-200 ease-in-out">Dashboard</a>
		                        	<a href="<?php echo wp_logout_url(); ?>" class="inline-block pl-4 hover:text-green transition-all duration-200 ease-in-out">Log Out</a>
		                    	</div>
		                    <?php endif; ?>
	                	</div>
	                <?php else : ?>
	                	<div class="space-x-2 px-4 py-2">
	                		<a href="/create-account" class="inline-block bg-blue hover:bg-white hover:text-blue border border-blue px-4 py-2 rounded-full text-white font-bold uppercase tracking-wider text-sm transition-all duration-200 ease-in-out">Create Account</a><a href="/login" class="inline-block bg-green hover:bg-white hover:text-green border border-green px-4 py-2 rounded-full text-white font-bold uppercase tracking-wider text-sm transition-all duration-200 ease-in-out">Returning User</a>
	                	</div>
	                <?php endif; ?>
	            </div>
	        </div>
	    </div>
	    <div class="container mx-auto">
	        <div class="flex justify-between items-center">
	            <div>
	                <!-- Logo -->
	                <a href="/" class="block p-4 w-48 md:w-64">
	                    <img src="https://smiadviser.org/wp-content/themes/smi/assets/img/logo.svg" alt="Logo" class="w-full" />
	                </a>
	            </div>
	            <div class="flex items-center flex-wrap justify-end w-64 lg:w-auto">
	                <span class="flex-none inline-block w-full px-4 mb-4 text-text font-bold tracking-wider text-center text-sm lg:mb-0 lg:text-left lg:w-auto">An APA and SAMHSA Initiative</span>
	                <span class="inline-block w-32 px-4">
	                    <a href="https://www.psychiatry.org/" target="_blank" class="inline-block" title="Go to the main American Psychiatric Association website">
	                        <img src="https://smiadviser.org/wp-content/themes/smi/golden-age/assets/img/logos/apa-logo-black.png" alt="American Psychiatric Association logo">
	                    </a>
	                </span>
	                <span class="inline-block w-32 px-4">
	                    <a href="https://www.samhsa.gov/" target="_blank" class="inline-block" title="Go to the main SAMHSA website">
	                        <img src="https://smiadviser.org/wp-content/themes/smi/golden-age/assets/img/logos/samhsa-logo-black.png" alt="Substance Abuse and Mental Health Services Administration (SAMHSA) logo">
	                    </a>
	                </span>
	            </div>
	        </div>
	    </div>
	</header>
	<section>
	    <div class="bg-main">
	        <div class="grid grid-cols-2 gap-0">
	            <div class="relative col-span-2 lg:col-span-1 text-text py-20 px-4 md:px-8 text-center lg:text-left bg-no-repeat bg-cover bg-center" style="background-image: url('https://consult.smiadviser.org/app/uploads/2023/08/professional.jpg');">
	            	<div class="pointer-events-none bg-main opacity-75 lg:opacity-100 absolute top-0 left-0 right-0 bottom-0 z-10"></div>
	            	<div class="relative z-20">
	            		<div class="space-y-4">
		                	<h1 class="font-bold text-2xl lg:text-3xl text-title">Request a Free Clinician-to-Clinician Consult</h1>
			                <p class="font-bold">Submit questions to our national experts in serious mental illness (SMI). This service is available to ALL mental health professionals! Find answers that are confidential, completely free, and evidence-based.</p>
			                <ul>
			                    <li>Ask us about psychopharmacology, therapies, recovery supports, patient and family engagement, education, and much more.</li>
			                    <li>Receive guidance from an expert within one business day</li>
			                </ul>
		                </div>
		                <div class="rounded-lg border-title border flex items-center p-4 mt-12 mb-8">
		                    <span class="flex-none text-title h-full mr-4"><?php echo get_icon('alert'); ?></span>
		                    <p class="text-xs text-title">
		                        If you are not a Mental Health Professional, please visit the Individual & Families section of the website. This includes individuals receiving care, family members, and others in the general public. SMI Adviser can not answer any questions submitted by anyone who is not a clinician.
		                    </p>
		                </div>
		                <hr  class="border-green">
							<p class="font-bold mt-4">For Demo Purposes, please <a href="/wp/wp-login.php?redirect_to=https%3A%2F%2Fconsult.smiadviser.org%2Fsubmit-consult" class="underline hover:text-green">click here</a> for Support Users.</p>
							<p class="font-bold">For Demo Purposes, please <a href="/wp/wp-login.php?redirect_to=https%3A%2F%2Fconsult.smiadviser.org%2Fmoderator-dashboard" class="underline hover:text-green">click here</a> for Support Agents.</p>
	            	</div>
	            </div>
	            <div class="col-span-2 lg:col-span-1 bg-no-repeat bg-cover bg-right relative" style="background-image: url('https://consult.smiadviser.org/app/uploads/2023/08/professional.jpg');">
	                <div class="slanted-bg"></div>
	            </div>
	        </div>
	    </div>
	</section>

	<section>
		<div class="max-w-4xl mx-auto">
			<div class="py-20 px-4 md:px-8">
				<div class="relative aspect-video">
					<iframe class="absolute top-0 left-0 w-full h-full" src="https://www.youtube.com/embed/xs0x1h5d83Q" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
				</div>
				<div class="text-center text-green mt-4 text-sm font-semibold">
					<p>Watch our video to see how it works. Then request a <strong>FREE</strong> consultation from our national experts in SMI.</p>
				</div>
			</div>
		</div>
	</section>
	<section class="bg-main">
		<div class="container mx-auto">
			<div class="py-20 px-4 md:px-8">
				<div class="text-center mb-8">
				  <h3 class="text-3xl mb-2 font-bold">Consult FAQS</h3>
				  <p>Have questions about requesting a consultation? See quick answers below.</p>
				</div>
				<div x-data="{ openTab: null }">
				  <script>
				    const faqs = [
				      {
				        question: 'Who can request a consultation?',
				        answer: 'Any mental health professional in clinical practice setting can request a consultation. This includes physicians, nurses, psychologists, social workers, physician assistants, counselors, administrators, case managers, and peer specialists.',
				      },
				      {
				        question: 'Who answers questions that I submit?',
				        answer: 'All questions are answered by a Clinical Expert Team of national experts in SMI. They represent Emory University, Harvard Medical School, Mental Health America, National Alliance on Mental Illness, University of California - Los Angeles, and University of Texas - Austin, and other institutions.',
				      },
				      {
				        question: 'How will I receive answers to my questions?',
				        answer: 'Once submitted, all of your questions are listed in your consultation dashboard and you receive email notifications when there is activity on any of your consultations. Your consultation dashboard allows you to access all consultations that are new, in progress, or closed.',
				      },
				      {
				        question: 'How many consultations can I request?',
				        answer: 'You can request as many consultations as you need on any topic related to serious mental illness (SMI). If you have questions about various topics, submit them separately as they may be answered by different experts on the Clinical Expert Team.',
				      },
				      {
				        question: 'What can I ask about?',
				        answer: 'Ask us about any topic related to serious mental illness. This includes psychopharmacology, alternative therapies, recovery supports, psychosocial interventions, patient and family engagement, education, and much more.',
				      },
				      {
				        question: 'Is this a secure service?',
				        answer: 'Yes, SMI Adviser is completely secure and your information is protected. All information is protected by an enterprise-level security infrastructure. The only individuals with access to your information are those directly affiliated with SMI Adviser, such as the Clinical Expert Team. Your questions will not be shared with other organizations.',
				      },
				      {
				        question: 'What happens to the information and questions I share?',
				        answer: 'SMI Adviser does not share any information you submit with any other organizations of any kind. The only individuals with access to your information are those directly affiliated with SMI Adviser, such as the Clinical Expert Team.',
				      },
				      {
				        question: 'Why do I have to login?',
				        answer: 'In order to ensure that you are a mental health professional, we have to ask you a few questions so we can verify your information such as your phone number and zip code. This also lets our Clinical Expert Team customize any evidence-based answers based on regional data or resources.',
				      },
				      {
				        question: 'Terms of Consult',
				        answer: 'SMI Adviser is provided for general educational and informational purposes. It should not be construed as medical advice in connection with the care of a particular patient or as a statement of the standard of care. It is not necessarily inclusive of all potential treatments. The activities and materials offered through SMI Adviser should not be considered as providing the basis for any certification or authorization to prescribe or administer medication. SMI Adviser is not intended to be a substitute for independent professional medical advice, diagnosis or treatment. SMI Adviser does not endorse or recommend any pharmaceutical or other treatment as an appropriate course for any particular patient or condition. The advice of a physician or other qualified health provider should be obtained for a treatment recommendation or to answer any questions regarding a medical condition. Never disregard professional medical advice or delay in seeking it because of something you have read or seen on SMI Adviser. SMI Adviser is provided as-is and is not guaranteed to be correct, complete or current. APA makes no warranty, expressed or implied, about the accuracy, applicability, fitness, completeness or reliability of the information contained within SMI Adviser. APA assumes no responsibility for any injury or damage to persons or property arising out of or related to any use of SMI Adviser or for any errors or omissions. The limitations applicable to the SMI Adviser consultation feature are set forth at: <a href="https://smiadviser.org/limitations-of-consultation" target="_blank" class="font-bold hover:text-blue transition-all duration-200 ease-in-out">https://smiadviser.org/limitations-of-consultation</a>',
				      },
				      // Add more question-answer pairs here
				    ];
				  </script>

				  <template x-for="(faq, index) in faqs" :key="index">
				    <div class="mb-4">
				      <button
				        class="flex justify-between items-center w-full p-4 bg-light-blue hover:bg-blue hover:text-white transition-all duration-200 ease-in-out focus:outline-none"
				        x-bind:class="{ 'bg-blue text-white': openTab === index }"
				        x-on:click="openTab = (openTab === index) ? null : index"
				      >
				        <span class="text-lg font-medium" x-text="faq.question"></span>
				        <span x-show="openTab === index" class="text-lg" x-cloak>-</span>
				        <span x-show="openTab !== index" class="text-lg" x-cloak>+</span>
				      </button>
				      <div
				        x-show="openTab === index"
				        class="p-4 bg-white shadow"
				        x-cloak
				      >
				        <p x-html="faq.answer"></p>
				      </div>
				    </div>
				  </template>
				</div>
			</div>
		</div>
	</section>
</main>
<footer class="bg-white py-8 px-4 text-sm text-text" x-data="{ open: false }">
	<div class="container mx-auto">
		<div class="flex flex-col md:flex-row justify-between items-center">
			<div>
				&copy; <?php echo date('Y'); ?> American Psychiatric Association. All rights reserved.
			</div>
			<div>
				<button x-on:click="open = true" class="inline-block p-2 transition-colors duration-150 ease-in-out hover:text-green">
	                Grant & Mission Statements
	            </button>
	            <a href="https://www.psychiatry.org/terms" target="_blank" class="inline-block p-2 transition-colors duration-150 ease-in-out hover:text-green">
	                Terms & Privacy
	            </a>
			</div>
		</div>
		
	</div>
	<!-- Modal -->
    <div x-show="open" class="fixed inset-0 flex items-center justify-center z-50" x-cloak>
        <div class="fixed inset-0 bg-black opacity-75"></div>
        <div class="relative bg-white p-8 max-w-5xl mx-auto">
            <h2 class="text-lg font-bold mb-2">Grant Statement</h2>
            <p class="mb-4">Funding for SMI Adviser was made possible by Grant No. SM080818 from SAMHSA of the U.S. Department of Health and Human Services (HHS). The contents are those of the author(s) and do not necessarily represent the official views of, nor an endorsement by, SAMHSA/HHS or the U.S. Government.</p>
            <hr>
            <h2 class="text-lg font-bold mb-2 mt-4">Mission Statement</h2>
            <p>Our mission is to advance the use of a person-centered approach to care that ensures people who have SMI find the treatment and support they need. For clinicians, we offer access to education, data, and consultations so you can make evidence-based treatment decisions. For patients, families, friends, people who have questions, or people who care for someone with SMI, we offer access to resources and answers from a national network of experts.</p>
            <button type="button" class="absolute top-0 right-0 p-4 text-gray-500 transform transition-all duration-200 ease-in-out hover:rotate-90" @click="open = false">
                <?php echo get_icon('x'); ?>
            </button>
        </div>
    </div>
    <!-- End Modal -->
</footer>
<?php get_footer(); ?>