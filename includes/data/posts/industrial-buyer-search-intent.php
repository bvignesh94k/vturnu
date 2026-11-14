<?php
/**
 * Guide: six families of industrial search, the buyer behind each one and the
 * page that answers it. Research-based version: no client query data is used.
 * Loaded by templates/blog-post.php through the 'rich' key.
 *
 * Survey figures are paraphrased from the reports linked in each sentence,
 * as recorded in docs/authority-series/research-industrial-and-linkedin.md
 * (pages read on 13 and 14 November 2026). All of them are vendor, agency or
 * trade research, and each figure carries its sample and edition. The six
 * families and the search-to-page map are our working model, not a survey
 * result. Example searches are illustrations. The Sai Impression facts are
 * exactly as published in includes/data/cases-real.php and are one example,
 * not a finding. Nothing here promises a ranking or an enquiry.
 */

$s = [
    'smte26' => 'https://advertising.globalspec.com/wp-content/uploads/2026/03/SMTE-Report_GS2602_FINAL.pdf',
    'smte25' => 'https://www.trewmarketing.com/hubfs/Technical%20Resources/Research/2025%20SMTE%20Research/PDF/2025%20State%20of%20Marketing%20to%20Engineers%20Research.pdf',
    'rhb26' => 'https://rhblake.com/manufacturing-buying-journey/',
    'rhb25' => 'https://cdn.base.parameter1.com/mindful/im/workspaces/default/uploads/2025/10/rh-blake-buying-journey-ebook-08122025.cRg3zH6gXB.pdf',
    'linkedin' => 'https://business.linkedin.com/content/dam/me/business/en-us/marketing-solutions/resources/pdfs/the-innovation-imperative.pdf',
];

return [

'body' => static function () use ($s): void { ?>
<p class="bx-open">An engineer who needs a part rarely starts by searching for a company. They search for a specification, an application or a problem. The company name comes later, if it comes at all.</p>
<p>Many manufacturer websites are built the other way round. They have a home page, an "our products" page and a brochure PDF. That structure answers one search: the company's own name. Every other search an engineer or a buyer makes has nowhere to land.</p>
<p>This guide is a map. It sorts industrial searches into six families, names the person behind each one, and matches each family to the page that answers it. Use it as a gap check: list your pages against the six families and see which searches your site cannot answer today.</p>

<?php px_answer('industrial-buyer-search-intent'); ?>

<?php px_toc([
    'research-before-contact' => 'Buyers research before they call',
    'six-families' => 'Six families of industrial search',
    'page-for-each-search' => 'The page that answers each search',
    'site-structure' => 'A site structure built on the map',
    'product-page' => 'What a product page needs',
    'country-searches' => 'Country and language searches',
    'measuring-enquiries' => 'Measuring enquiries from these pages',
    'faq-title' => 'FAQs',
]); ?>

<h2 id="research-before-contact">Buyers Research Before They Call</h2>
<p>The surveys below are all vendor, agency or trade research. The companies behind them sell marketing or advertising to industrial firms. None states its field dates. Read the figures as direction, not as measurement, and note the sample beside each one.</p>
<p>The <?= ext_link($s['smte26'], '2026 State of Marketing to Engineers') ?> report, by TREW Marketing and GlobalSpec, surveyed over one thousand engineers and technical professionals worldwide. On average, respondents said 62 percent of the buying process happens online before they speak to a supplier (709 answers). When they do make contact, the most common reasons were a pricing or stock question at 26 percent, wanting to check what they had found online at 23 percent, and technical complexity at 21 percent (708 answers).</p>
<p>So the first call is often a check on research that is already done. The same report asked which sources engineers use routinely.</p>

<?php px_chart('engineer-sources', 'Sources engineers say they use routinely', 'Percent of 783 respondents. 2026 State of Marketing to Engineers, TREW Marketing and GlobalSpec.', [
    ['label' => 'Online technical publications', 'value' => 76, 'text' => '76%'],
    ['label' => 'Supplier websites', 'value' => 74, 'text' => '74%'],
    ['label' => 'Print technical publications', 'value' => 53, 'text' => '53%'],
    ['label' => 'Sales or application engineers', 'value' => 41, 'text' => '41%'],
    ['label' => 'Industry directories', 'value' => 31, 'text' => '31%'],
    ['label' => 'YouTube', 'value' => 30, 'text' => '30%'],
    ['label' => 'Trade shows', 'value' => 27, 'text' => '27%'],
    ['label' => 'Webinars', 'value' => 27, 'text' => '27%'],
    ['label' => 'Newsletters', 'value' => 24, 'text' => '24%'],
    ['label' => 'Generative AI platforms', 'value' => 21, 'text' => '21%'],
    ['label' => 'LinkedIn', 'value' => 11, 'text' => '11%'],
], [
    'wide' => true, 'max' => 100, 'ticks' => [0, 25, 50, 75],
    'head' => ['Source', 'Share who use it routinely'],
    'source' => 'Source: ' . ext_link($s['smte26'], '2026 State of Marketing to Engineers') . ', TREW Marketing and GlobalSpec, page 13. Vendor research. Field dates are not stated. Search engines were not an answer option in this question.',
]); ?>

<p>Supplier websites sit almost level with technical publications. Search engines were not offered as an answer in that question, so the chart says nothing about search. Two other studies do.</p>
<ul class="post-list">
    <li><strong>Search is a common first step.</strong> In RH Blake's <?= ext_link($s['rhb25'], '2025 buyer journey study') ?> with Industrial Equipment News, which surveyed more than 250 manufacturing executives and managers, 45 percent named a search engine as their very first source. A vendor website came next at 23 percent, peers at 11 percent and generative AI at 5 percent. This is agency and trade publisher research.</li>
    <li><strong>A shortlist often exists before the first contact.</strong> In RH Blake's <?= ext_link($s['rhb26'], '2026 study') ?> with ISA and Automation.com, 81 percent of respondents had a shortlist before they contacted any vendor. That study has 121 respondents, so treat it as directional.</li>
    <li><strong>Vendor websites lead as a source of vendor information.</strong> <?= ext_link($s['linkedin'], 'LinkedIn\'s 2025 manufacturing buyer research') ?> with GWI surveyed 1,767 people who help choose manufacturing vendors, between 22 October and 5 November 2024. Vendor websites were the top source at 34 percent. This is vendor research.</li>
</ul>
<p><strong>AI tools now sit beside search, and have not replaced it.</strong> In the 2026 engineers report, 77 percent said they use a search engine more often than a generative AI tool, 8 percent said the reverse and 16 percent use both equally (753 answers). Asked how far they trust AI answers, respondents gave an average of 4.7 out of 10 (572 answers).</p>
<p>The practical reading is simple. Much of the choosing happens on websites, before sales hears about it. If your site does not answer the search, you are not on the shortlist that the first call is made from.</p>

<h2 id="six-families">Six Families of Industrial Search</h2>
<p>The six families below are our working model. They are a way to sort searches, not a result from any survey, and the example searches are illustrations. Your own Search Console data will show which families matter in your market.</p>
<p>The idea behind the model is that a buying group contains different people who search differently. A design engineer types a specification. A maintenance manager types a fault. A purchasing officer types a supplier and a place. One purchase can produce all six kinds of search over several months.</p>

<?php px_table('Six families of industrial search, who makes each one and how close it is to an enquiry', ['Family', 'Example search (illustration)', 'Who usually searches', 'What they need to see', 'Distance from an enquiry'], [
    ['1. Specification', '"316 stainless hex bolt m12 a4-80"', 'Design or project engineer', 'Exact values: material, size, tolerance, standard', 'Close, if the values match'],
    ['2. Application', '"gasket for high temperature steam line"', 'Engineer or plant manager', 'Proof the product suits the use, with operating limits', 'Middle'],
    ['3. Supplier', '"label manufacturers in india"', 'Purchasing or sourcing staff', 'What you make, where, capacity and who you supply', 'Close'],
    ['4. Compliance', '"iso 13485 certified injection moulder"', 'Quality or purchasing staff', 'The certificate, its scope and its expiry date', 'Close, as a filter'],
    ['5. Problem', '"why do thermal labels fade in cold storage"', 'Engineer, maintenance or production staff', 'A clear cause and a fix, written by someone technical', 'Far, but early'],
    ['6. Commercial', '"minimum order quantity custom labels"', 'Purchasing staff', 'Minimum order, lead time and how price is worked out', 'Very close'],
]); ?>

<p>Three points about reading the table.</p>
<ul class="post-list">
    <li><strong>Engineers and purchasing staff type different things.</strong> Engineers tend to search in the language of the part: values, standards and uses. Purchasing staff tend to search in the language of supply: who makes it, where, and on what terms. A site written only for one group misses the other.</li>
    <li><strong>Closeness to an enquiry is not the same as value.</strong> A problem search is far from a quote request. It is also where an engineer first meets a supplier who knows the subject.</li>
    <li><strong>Supplier, compliance and commercial searches signal a buyer near a request for quotation.</strong> The need is already defined. The searcher is now choosing who to ask.</li>
</ul>

<p class="bx-note"><strong>One published example.</strong> Sai Impression manufactures labels in Chennai. Its rebuilt site has 105 pages in the sitemap, with product, industry and blog sections and fifteen long-form articles. In the three months to 29 October 2026, Search Console showed 92.9K impressions and 1.32K clicks. Two of the searches named in the <a href="/case-studies/sai-impression-label-manufacturer/">Sai Impression case study</a> are "label manufacturers in india" and "label stock manufacturers in india". Both belong to the supplier family. We have not published a breakdown by family or by page type, so this is one example of the third row and nothing more.</p>

<h2 id="page-for-each-search">The Page That Answers Each Search</h2>
<p>Each family needs its own kind of page. A search engine, or an AI assistant, is looking for a page whose subject matches the search. A general "products" page matches none of them well.</p>

<?php px_table('The search-to-page map: one page type for each family', ['Search family', 'Page that answers it', 'What the page must contain', 'A common mistake'], [
    ['Specification', 'Product family page', 'A table of specifications as text, with units, standards and variants', 'Specifications locked in a PDF or an image'],
    ['Application', 'Application or industry page', 'The use, the operating conditions, the products that suit it and why', 'One page listing every industry in a sentence each'],
    ['Supplier', 'Capability and machinery page', 'Processes, machines, capacity, materials handled and locations served', 'An "about us" page with history and no capability'],
    ['Compliance', 'Certification page', 'Each certificate by name, its scope, the issuing body and the date', 'A row of logos with no text'],
    ['Problem', 'Technical article', 'The cause, the fix and the limits, with a named technical author', 'A sales page dressed up as an article'],
    ['Commercial', 'Request for quotation page', 'Minimum order, typical lead time, the basis of pricing and the fields a quote needs', 'A general contact form in place of an RFQ form'],
]); ?>

<p>Two of those mistakes deserve a closer look.</p>
<h3>One page for forty products</h3>
<p>A single page that lists every product can only have one title and one main subject. It cannot match "food grade conveyor belt" and "heat resistant conveyor belt" equally. Group products into families that a buyer would recognise, and give each family a page. A family page with its variants in a table is usually enough. A page for every stock code is rarely needed.</p>
<h3>A contact form in place of an RFQ form</h3>
<p>A buyer ready to request a quote has details to give: quantity, material, dimensions, drawings, delivery place and date. A form with "name, email, message" makes them write all of that from scratch, and makes your sales team ask for it again. An RFQ form that asks for those fields, and accepts a drawing, is quicker for both sides. Our guide to <a href="/blog/what-to-write-on-every-page-of-a-b2b-website/">what to write on every page of a B2B website</a> covers the wording of each page type.</p>

<?php px_cta_mid([
    'label' => 'Free industry SEO audit',
    'art' => 'scan',
    'h' => 'See Which of the Six Searches Your Site Has No Page For',
    'p' => 'Ask for a free industry SEO audit. We list your pages against the six search families and show which searches have nowhere to land.',
    'href' => '/lead-gen-seo-for-business-industrial/#enquire',
    'btn' => 'Get My Free Industry SEO Audit',
    'cta' => 'blog-industrial-audit',
]); ?>

<h2 id="site-structure">A Site Structure Built on the Map</h2>
<p>Put the six page types together and you have a structure for a manufacturer's website. The diagram shows the sections and what each one is for. It is a starting pattern. A distributor or an engineering services firm would rename some sections and keep the logic.</p>

<?php px_flow('manufacturer-site', 'A manufacturer website, section by section', [
    ['Product families', 'One page per family, with specifications as text and a table of variants.', 'Specification searches'],
    ['Applications and industries', 'One page per use or sector, linking to the product families that suit it.', 'Application searches'],
    ['Capabilities and machinery', 'Processes, machines, capacity and the locations you supply.', 'Supplier searches'],
    ['Certifications', 'Each certificate with its scope, issuing body and date.', 'Compliance searches'],
    ['Technical articles', 'Causes and fixes for the problems your products solve, with a named author.', 'Problem searches'],
    ['Request for quotation', 'Minimum order, lead time, pricing basis and a form built for quotes.', 'Commercial searches'],
], [
    'sub' => 'Every section links to the request for quotation page. Product, application and article pages link to each other.',
    'cap' => 'The tag beside each section is the search family it answers. This is a working model, not a rule.',
]); ?>

<p>The links between sections matter as much as the sections. An engineer who lands on a technical article should be one click from the product family it concerns. A buyer on a product page should be one click from the certificate and the RFQ form.</p>

<h2 id="product-page">A Product Page Has to Be Read by a Person and a Machine</h2>
<p>For an industrial buyer, the specification is the content. It is not an attachment to the content. The 2025 edition of the <?= ext_link($s['smte25'], 'State of Marketing to Engineers') ?> report asked which content engineers value most. Datasheets came first at 79 percent, ahead of technical publication articles at 61 percent and CAD drawings at 37 percent (1,018 answers). The 2026 edition did not repeat that question.</p>
<p>A datasheet that exists only as a PDF serves the person who has already found you. It does much less for the person still searching, because the values are harder for a search engine or an AI crawler to read and to connect to your product page. Keep the PDF, and put the same data on the page.</p>
<p>A product family page should carry:</p>
<ul class="post-list">
    <li><strong>Specifications as text and tables.</strong> Real HTML tables, not images of tables.</li>
    <li><strong>Units, written out.</strong> State metric and imperial where your buyers use both.</li>
    <li><strong>Standards by their full reference.</strong> The standard's number and year, as a buyer would type it.</li>
    <li><strong>Variants.</strong> Sizes, grades and finishes in one table, so a near match can be found.</li>
    <li><strong>Documents.</strong> The datasheet, the drawing and the certificate, each with a described link.</li>
    <li><strong>Limits.</strong> What the product is not suitable for. This saves a wasted enquiry on both sides.</li>
    <li><strong>A clear route to a quote.</strong> A link to the RFQ form that carries the product name with it.</li>
</ul>
<p>Two technical checks apply here. The text has to be in the HTML the server sends, which our guide to <a href="/blog/ai-crawler-access-problems/">AI crawler access problems</a> shows how to test. And product data can be marked up so that machines read it as data, which <a href="/blog/schema-markup-for-b2b-websites/">schema markup for B2B websites</a> explains. If writing these pages is the obstacle, see our <a href="/product-description-writing-services/">product description writing</a> and <a href="/technical-content-writing-services/">technical content writing</a> services.</p>

<h2 id="country-searches">Country and Language Searches Change the Page Only Sometimes</h2>
<p>Buyers often add a place to a supplier search: "in India", "in Germany", "near Pune". Whether that calls for a separate page depends on what changes for the buyer in that place.</p>
<?php px_table('When a place in the search needs its own page', ['Situation', 'A separate page?', 'What to do'], [
    ['You make the product in that country and buyers search for it there', 'No. Your main pages should already say where you manufacture', 'State the location in the page text, the capability page and the business details'],
    ['You export to a country, and terms differ there', 'Yes, if you have something specific to say', 'Cover delivery terms, lead time, local standards and any local stockist'],
    ['The country uses another language', 'Yes, in that language', 'Translate properly and mark up the versions so search engines can tell them apart'],
    ['You want to rank in a place you do not serve', 'No', 'A page with a place name and nothing true to say about it helps nobody'],
]); ?>
<p>Standards are the detail most often missed. A buyer in Germany may search by a DIN or EN reference where a buyer in the United States searches by ASTM. If your product meets both, name both on the page. For sites with several countries or languages, our <a href="/blog/international-seo-checklist/">international SEO checklist</a> covers the technical setup.</p>

<h2 id="measuring-enquiries">Measure Enquiries by Landing Page and Search Family</h2>
<p>Total traffic is a poor measure for an industrial site. A technical article can bring many readers who will never buy, and an RFQ page can bring few visitors who nearly all do. Averaging them hides both facts.</p>
<p>Report three things instead:</p>
<ol class="bx-steps">
    <li><strong>Requests for quotation by landing page.</strong> Record the first page each enquirer arrived on, and count quote requests against it.</li>
    <li><strong>Impressions and clicks by search family.</strong> Tag each page with the family it answers, then group Search Console data by tag. A family with no impressions is a family with no page, or a page that is not being found.</li>
    <li><strong>The share of quote requests that sales accepts.</strong> A request for a product you do not make is not a lead.</li>
</ol>
<p>Search Console shows queries by page. It does not tie a query to a named enquirer, so the join is made at the landing page. Our framework for <a href="/blog/measure-qualified-leads-from-organic-search/">measuring qualified leads from organic search</a> gives the fields to store and the formulas. For the wider picture of how business buyers search, see the <a href="/guides/complete-guide-to-business-lead-gen-seo/">complete guide to business lead generation SEO</a>.</p>
<p>Expect the numbers to move slowly. Industrial purchases involve several people. In the 121-respondent RH Blake study cited above, 83 percent of purchases involved five or more evaluators. One enquiry may follow months of searches by people you never see.</p>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">Final Thoughts</h2>
<p>Industrial buyers do a large part of their choosing on websites, before a supplier knows they exist. What they type is rarely a company name. It is a value, a use, a standard, a fault or a term of supply.</p>
<p>The map in this guide turns that into a plain test. Write down the six families. Beside each, write the page on your site that answers it. Where the line is blank, a search has nowhere to land. Filling the gap does not guarantee that anyone finds the page, but an empty line guarantees that nobody can.</p>

<?php px_cta_main([
    'h' => 'Want Your Pages Mapped to How Your Buyers Search?',
    'p' => 'Tell us what you make and who buys it. We will show you which searches your site answers today and which it does not.',
    'btn' => 'Talk to a Strategist',
    'cta' => 'blog-industrial-contact',
    'link' => ['/lead-gen-seo-for-business-industrial/', 'See Industrial SEO Services', 'blog-industrial-service'],
]); ?>

<?php px_author(); ?>
<?php },

];
