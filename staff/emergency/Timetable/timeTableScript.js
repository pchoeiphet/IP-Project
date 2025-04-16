// รอให้เอกสารโหลดเสร็จก่อนที่จะเริ่มทำงาน
// FullCalendar จะไม่เริ่มทำงานจนกว่าหน้าเว็บจะโหลดเสร็จสมบูรณ์
document.addEventListener('DOMContentLoaded', function () {
    
    // ค้นหา element ที่มี id เป็น 'calendar' ซึ่งเป็นตำแหน่งที่จะนำปฏิทินมาแสดง
    var calendarEl = document.getElementById('calendar');
    
    // สร้างอินสแตนซ์ของ FullCalendar โดยกำหนดค่าต่าง ๆ ที่จำเป็น
    var calendar = new FullCalendar.Calendar(calendarEl, {
        
        // กำหนดให้ปฏิทินแสดงผลเริ่มต้นเป็นแบบรายสัปดาห์
        initialView: 'timeGridWeek',
        
        // เปิดให้สามารถลากและแก้ไข event ได้
        editable: true, 
        
        // เปิดให้สามารถเลือกเวลาบนปฏิทินได้
        selectable: true, 
        
        // ตั้งค่าเขตเวลาให้ตรงกับประเทศไทย
        timeZone: 'Asia/Bangkok', 

        // กำหนดแถบเครื่องมือด้านบนของปฏิทิน
        headerToolbar: {
            left: 'prev,next today',  // ปุ่มสำหรับเลื่อนดูวันก่อนหน้า ถัดไป และปุ่มไปที่วันนี้
            center: 'title', // แสดงชื่อเดือนหรือช่วงเวลาปัจจุบัน
            right: 'timeGridWeek,timeGridDay' // ปุ่มสลับมุมมองเป็นรายวันหรือรายสัปดาห์
        },

        // กำหนดให้ event ถูกแสดงเป็นบล็อกเต็ม
        eventDisplay: 'block',
        
        // ตั้งค่าให้แสดงเวลาแบบ 24 ชั่วโมง (ไม่ใช้ AM/PM)
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false }, 

        // กำหนดการดึงข้อมูล event จากไฟล์ PHP ที่ใช้ดึงข้อมูลจากฐานข้อมูล
        events: {
            url: 'fetch_events.php', // ดึงข้อมูลจากไฟล์ PHP
            
            // เมื่อเกิดข้อผิดพลาดในการโหลดข้อมูลจะแสดงข้อความแจ้งเตือน
            failure: function () {
                alert('There was an error while fetching events!');
            },
            
            // เมื่อโหลดข้อมูลสำเร็จจะทำการกำหนดสีของ event ตามประเภท
            success: function (data) {
                console.log('Fetched events:', data); // แสดงข้อมูล event ใน console เพื่อตรวจสอบ
                
                data.forEach(event => {
                    if (event.type === 'ambulance') {
                        if (event.status === 'ปฏิบัติเสร็จสิ้นแล้ว') {
                            event.color = '#999'; // สีเทา
                            event.borderColor = '#777';
                        } else {
                            event.color = '#3498DB'; // สีฟ้า
                            event.borderColor = '#1F618D';
                        }
                    } else if (event.type === 'event') {
                        event.color = '#9B59B6'; // สีม่วงสำหรับงานทั่วไป
                        event.borderColor = '#6C3483'; // กำหนดสีกรอบให้เข้มขึ้น
                    }
                });
            },
            
            // ถ้ามีข้อผิดพลาดจะแสดงข้อมูลใน console เพื่อตรวจสอบ
            error: function (xhr, status, error) {
                console.error('Error fetching events:', error);
                console.error('Response:', xhr.responseText);
            }
        },

        // เมื่อผู้ใช้ขยายขนาด event (resize) จะทำการอัปเดตเวลาสิ้นสุดในฐานข้อมูล
        eventResize: function (info) {
            updateEventFinishTime(info.event);
        },

        // เมื่อผู้ใช้ลาก event ไปตำแหน่งใหม่ จะอัปเดตเวลาทั้งหมดของ event
        eventDrop: function (info) {
            updateEventFinishTime(info.event);
        },

        eventClick: function (info) {
            const event = info.event;
          
            // ตรวจสอบว่าเป็นประเภท ambulance เท่านั้น
            if (event.extendedProps.type === 'ambulance') {
              if (confirm(`คุณต้องการเปลี่ยนสถานะงานนี้เป็น "ปฏิบัติภารกิจเสร็จสิ้นแล้ว" ใช่หรือไม่?`)) {
                $.ajax({
                  url: 'update_ambulance_status.php',
                  method: 'POST',
                  data: {
                    id: event.id // ใช้ id จาก event ที่ FullCalendar มีอยู่
                  },
                  success: function (response) {
                    alert('อัปเดตสถานะเรียบร้อยแล้ว');
                    event.setProp('color', '#999'); 
                  },
                  error: function () {
                    alert('เกิดข้อผิดพลาดในการอัปเดตสถานะ');
                  }
                });
              }
            }
          }
          
    });

    // แสดงปฏิทินบนหน้าเว็บ
    calendar.render();

    // ฟังก์ชันสำหรับอัปเดตเวลาสิ้นสุดของ event ลงในฐานข้อมูลผ่าน AJAX
    function updateEventFinishTime(event) {
        $.ajax({
            url: 'update_finish_time.php', // เรียกใช้งาน PHP script ที่ใช้บันทึกเวลาสิ้นสุดของ event
            method: 'POST',
            data: {
                id: event.id,
                type: event.extendedProps.type, // ประเภทของ event (ambulance หรือ event)
                newStartTime: event.start.toISOString(), // เวลาเริ่มต้นใหม่ (ถ้ามีการลาก)
                newEndTime: event.end.toISOString() // เวลาสิ้นสุดใหม่ที่ถูกอัปเดต
            },
            
            // เมื่อการอัปเดตเสร็จสิ้นจะแสดงข้อความแจ้งเตือนให้ผู้ใช้ทราบ
            success: function (response) {
                console.log('Event updated:', response);
                alert('Updated event time successfully!');
            },
            
            // ถ้ามีข้อผิดพลาดในการอัปเดตจะแสดงข้อมูลข้อผิดพลาดใน console
            error: function (xhr, status, error) {
                console.error('Error updating event:', error);
                console.error('Response:', xhr.responseText);
                alert('There was an error updating the event!');
            }
        });
    }
    
});

