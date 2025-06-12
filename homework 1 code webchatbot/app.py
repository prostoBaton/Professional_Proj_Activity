import os
import sys
import asyncio
import nest_asyncio
from dotenv import load_dotenv
import streamlit as st
from streamlit_chat import message as st_message
from helpmy import timeres
from helpmy import qes
import numpy as np
import pandas as pd

import time
# импорт собственных классов для веб-скрапинга, суммаризации, создания эмбеддингов и чатбота
from scrap.scrapper import WebScrapper
from rag.summarization import WebSummarizer
from rag.ingest import EmbeddingIngestor
from rag.chatbot import ChatBot

# Для Windows ставим соответствующую политику цикла событий asyncio (нужно для совместимости)
if sys.platform == "win32":
    asyncio.set_event_loop_policy(asyncio.WindowsProactorEventLoopPolicy())

# загружаем переменные окружения из .env файла
load_dotenv()

# исправляем ограничение asyncio, чтобы можно было вызывать run внутри уже запущенного цикла
nest_asyncio.apply()

# инициализация переменных сессии Streamlit, чтобы состояние хранилось между действиями
if "url_submitted" not in st.session_state:
    st.session_state.url_submitted = False
if "extraction_done" not in st.session_state:
    st.session_state.extraction_done = False
if "extracted_text" not in st.session_state:
    st.session_state.extracted_text = ""
if "embedding_done" not in st.session_state:
    st.session_state.embedding_done = False
if "vectorstore" not in st.session_state:
    st.session_state.vectorstore = None
if "chat_history" not in st.session_state:
    st.session_state.chat_history = []
if "summary" not in st.session_state:
    st.session_state.summary = ""

# отдельные переменные для работы со «Story» — чтобы разделить с основной логикой
if "story_embedding_done" not in st.session_state:
    st.session_state.story_embedding_done = False
if "story_vectorstore" not in st.session_state:
    st.session_state.story_vectorstore = None
if "story_chat_history" not in st.session_state:
    st.session_state.story_chat_history = []

# ---------------------------
# Конфигурация страницы Streamlit
# ---------------------------
st.set_page_config(layout="wide", page_title="Web-ChatBot")
st.title("Project Chatbot with LLM + RAG")

# ---------------------------
# Интерфейс с выбором страниц в сайдбаре
# ---------------------------
page = st.sidebar.selectbox("Menu", ["Home", "AI Chatbot", "Archive", "Story"])

if page == "Home":
    # приветственный текст и описание возможностей проекта
    st.markdown(
        """
        **Hello, user!**
        ## Welcome to Web-Chatbot
        **Web-Chatbot** is a small chatbot empowered by integration of LLM with RAG of website knowledge extraction through LangChain.
        
        **Functionalities:**
        - **Web Scraping:** Crawl and extract web page content.
        - **Web Summarization:** Generate detailed summaries of the extracted content.
        - **Create Embeddings:** Embeddings with FAISS for vector representation and retrieval of web-scraped information
        - **Chatbot Interface:** Execute Question-Answering task via a conversational agent.

        **Technologies:**
        - **LLM:** Model deepseek-r1:1.5b
        - **FAISS:** vector database to store embeddings
        - **LangChain:** framework to integrate LLM, external
        - **Streamlit:** python library to fast prototype web apps
        
        Get started!
        """
    )    

elif page == "AI Chatbot":
    # Форма для ввода URL сайта для скрапинга
    with st.form("url_form"):
        url_input = st.text_input("Enter a URL to crawl:")
        submit_url = st.form_submit_button("Submit URL")

        if submit_url and url_input:
            # при отправке URL сбрасываем нужные состояния, чтобы начать заново
            st.session_state.url_submitted = True
            st.session_state.extraction_done = False
            st.session_state.embedding_done = False
            st.session_state.chat_history = []
            st.session_state.summary = ""
    
    if st.session_state.url_submitted:
        # выбор модели ИИ
        ChosenModel = st.selectbox("Choose AI model to use:", ["Deepseek", "Qwen", "gemma3", "Llama"])

        col1, col2 = st.columns(2)

        with col1:
            st.header("1. Web-Scrapping")

            # если ещё не скрапили, запускаем процесс извлечения текста с сайта
            if not st.session_state.extraction_done:
                with st.spinner("Extracting website..."):
                    scraper = WebScrapper()
                    # запускаем асинхронный парсинг сайта
                    extracted = asyncio.run(scraper.crawl(url_input))
                    st.session_state.extracted_text = extracted
                    st.session_state.extraction_done = True
                st.success("Extraction complete!")

            # показываем превью извлечённого текста (первые 5 непустых строк)
            preview = "\n".join([line for line in st.session_state.extracted_text.splitlines() if line.strip()][:5])
            st.text_area("Extracted Text Preview", preview, height=150)

            # кнопка для скачивания извлечённого текста
            st.download_button(
                label="Download Extracted Text",
                data=st.session_state.extracted_text,
                file_name="extract_text.txt",
                mime="text/plain",
            )

            st.markdown("---")

            st.header("2. Web-Summarization")

            # кнопка для суммаризации извлечённого текста
            if st.button("Summarize Web Page", key="summarize_button"):
                with st.spinner("Summarizing..."):
                    summarizer = WebSummarizer()
                    st.session_state.summary = summarizer.summarize(st.session_state.extracted_text)
                st.success("Summarization complete!")

            # выводим результат суммаризации
            if st.session_state.summary:
                st.subheader("Summarized Output")
                st.markdown(st.session_state.summary, unsafe_allow_html=False)

        with col2:
            st.header("3. Create Embeddings")

            # создаём вектора представления (эмбеддинги) если текст извлечён, но ещё не созданы эмбеддинги
            if st.session_state.extraction_done and not st.session_state.embedding_done:
                if st.button("Create Embeddings"):
                    with st.spinner("Creating embeddings..."):
                        embeddings = EmbeddingIngestor()
                        st.session_state.vectorstore = embeddings.create_embeddings(st.session_state.extracted_text)
                        st.session_state.embedding_done = True
                    st.success("Vectors are created!")

            elif st.session_state.embedding_done:
                st.info("Embeddings have been created.")

            st.markdown("---")

            st.header("4. ChatBot: " + ChosenModel)

            # если эмбеддинги созданы — запускаем чатбота с выбранной моделью
            if st.session_state.embedding_done:
                chatbot = ChatBot(st.session_state.vectorstore, ChosenModel)
                user_input = st.text_input("Your Message:", key="chat_input")

                # отправка запроса и получение ответа от чатбота
                if st.button("Send", key="send_button") and user_input:
                    startt = time.time()
                    bot_answer = chatbot.qa(user_input)
                    # сохраняем историю диалога в сессии
                    st.session_state.chat_history.append({"user": user_input, "bot": bot_answer})
                    # пишем историю в файл для сохранения
                    chat_file_content = "\n\n".join([f"User: {chat['user']}\nBot: {chat['bot']}" for chat in st.session_state.chat_history])
                    with open("history/chat_history.txt", "w", encoding="utf-8") as cf:
                        cf.write(chat_file_content)
                    kt = time.time()
                    ress = round(kt - startt, 2)
                    # сохраняем время ответа и вопрос в глобальные списки для статистики
                    timeres[ChosenModel].append(ress)
                    qes[ChosenModel].append(user_input)
                    st.info("Time passed: " + str(ress))
                # показываем чат с историей
                if st.session_state.chat_history:
                    for chat in st.session_state.chat_history:
                        st_message(chat["user"], is_user=True)
                        st_message(chat["bot"], is_user=False)
                
            else:
                st.info("Please create embeddings to activate the chat.")

elif page == "Archive":
    # отображение архива вопросов и времени ответа по каждой модели
    st.markdown(
        """
        ## Chatbot Archive
        """
    )
    if st.session_state.embedding_done != None:
        st.info("embedding_done")

    col1, col2, col3, col4 = st.columns(4)
    
    with col1:
        st.header("Deepseek")
        for i in range(len(qes["Deepseek"])):
            st.caption(str(qes["Deepseek"][i]))
            st.info(str(timeres["Deepseek"][i]))
    with col2:
        st.header("Qwen")
        for i in range(len(qes["Qwen"])):
            st.caption(str(qes["Qwen"][i]))
            st.info(str(timeres["Qwen"][i]))
    with col3:
        st.header("gemma3")
        for i in range(len(qes["gemma3"])):
            st.caption(str(qes["gemma3"][i]))
            st.info(str(timeres["gemma3"][i]))
    with col4:
        st.header("Llama")
        for i in range(len(qes["Llama"])):	
            st.caption(str(qes["Llama"][i]))
            st.info(str(timeres["Llama"][i]))

elif page == "Story":
    # логика работы с отдельной историей (файл unfath.txt)
    if not st.session_state.story_embedding_done:
        st.session_state.story_embedding_done = False
        st.session_state.story_chat_history = []
    ChosenModel = "Deepseek"
    
    st.header("3. Create Embeddings")

    # создаём эмбеддинги для текста истории
    if not st.session_state.story_embedding_done:
        if st.button("Create Embeddings"):
            with st.spinner("Creating embeddings..."):
                with open("unfath.txt", 'r') as f:
                    embeddings = EmbeddingIngestor()
                    st.session_state.story_vectorstore = embeddings.create_embeddings(f.read())
                    st.session_state.story_embedding_done = True
            st.success("Vectors are created!")

    elif st.session_state.story_embedding_done:
        st.info("Embeddings have been created.")

    st.markdown("---")

    st.header("ChatBot: " + ChosenModel)

    # чатбот для истории
    if st.session_state.story_embedding_done:
        chatbot = ChatBot(st.session_state.story_vectorstore, ChosenModel)
        user_input = st.text_input("Your Message:", key="chat_input")

        if st.button("Send", key="send_button") and user_input:
            startt = time.time()
            bot_answer = chatbot.qa(user_input)
            st.session_state.story_chat_history.append({"user": user_input, "bot": bot_answer})
            # сохраняем историю в файл
            chat_file_content = "\n\n".join([f"User: {chat['user']}\nBot: {chat['bot']}" for chat in st.session_state.story_chat_history])
            with open("history/story_chat_history.txt", "w", encoding="utf-8") as cf:
                cf.write(chat_file_content)
            # показываем переписку
            if st.session_state.story_chat_history:
                for chat in st.session_state.story_chat_history:
                    st_message(chat["user"], is_user=True)
                    st_message(chat["bot"], is_user=False)
    else:
        st.info("Please create embeddings to activate the chat.")
